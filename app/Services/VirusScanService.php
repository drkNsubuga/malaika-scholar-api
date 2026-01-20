<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class VirusScanService
{
    private string $scanMethod;
    private array $config;

    public function __construct()
    {
        $this->scanMethod = config('security.virus_scan.method', 'basic');
        $this->config = config('security.virus_scan', []);
    }

    /**
     * Scan uploaded file for viruses and malware
     */
    public function scanFile(UploadedFile $file): array
    {
        try {
            switch ($this->scanMethod) {
                case 'clamav':
                    return $this->scanWithClamAV($file);
                case 'virustotal':
                    return $this->scanWithVirusTotal($file);
                case 'aws_guardduty':
                    return $this->scanWithAWSGuardDuty($file);
                case 'basic':
                default:
                    return $this->basicSecurityScan($file);
            }
        } catch (\Exception $e) {
            Log::error('Virus scan failed', [
                'method' => $this->scanMethod,
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);

            // In case of scan failure, apply basic security checks
            return $this->basicSecurityScan($file);
        }
    }

    /**
     * Basic security scan (file type, size, content checks)
     */
    private function basicSecurityScan(UploadedFile $file): array
    {
        $issues = [];

        // Check file size (prevent zip bombs)
        if ($file->getSize() > 50 * 1024 * 1024) { // 50MB
            $issues[] = 'File too large';
        }

        // Check MIME type vs extension mismatch
        $mimeType = $file->getMimeType();
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (!$this->validateMimeTypeExtension($mimeType, $extension)) {
            $issues[] = 'MIME type and extension mismatch';
        }

        // Check for embedded executables in documents
        if ($this->containsExecutableSignatures($file)) {
            $issues[] = 'Contains executable signatures';
        }

        // Check file header/magic bytes
        if (!$this->validateFileHeader($file)) {
            $issues[] = 'Invalid file header';
        }

        return [
            'clean' => empty($issues),
            'method' => 'basic',
            'issues' => $issues,
            'scanned_at' => now()->toISOString()
        ];
    }

    /**
     * Scan with ClamAV antivirus
     */
    private function scanWithClamAV(UploadedFile $file): array
    {
        $clamavSocket = $this->config['clamav']['socket'] ?? '/var/run/clamav/clamd.ctl';
        
        if (!file_exists($clamavSocket)) {
            Log::warning('ClamAV socket not found, falling back to basic scan');
            return $this->basicSecurityScan($file);
        }

        try {
            // Create socket connection
            $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
            if (!socket_connect($socket, $clamavSocket)) {
                throw new \Exception('Cannot connect to ClamAV daemon');
            }

            // Send INSTREAM command
            socket_write($socket, "zINSTREAM\0");
            
            // Send file data in chunks
            $handle = fopen($file->getPathname(), 'rb');
            while (!feof($handle)) {
                $chunk = fread($handle, 8192);
                $size = pack('N', strlen($chunk));
                socket_write($socket, $size . $chunk);
            }
            fclose($handle);
            
            // Send end of stream
            socket_write($socket, pack('N', 0));
            
            // Read response
            $response = socket_read($socket, 1024);
            socket_close($socket);

            $clean = strpos($response, 'OK') !== false;
            
            return [
                'clean' => $clean,
                'method' => 'clamav',
                'response' => trim($response),
                'scanned_at' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            Log::error('ClamAV scan failed', ['error' => $e->getMessage()]);
            return $this->basicSecurityScan($file);
        }
    }

    /**
     * Scan with VirusTotal API
     */
    private function scanWithVirusTotal(UploadedFile $file): array
    {
        $apiKey = $this->config['virustotal']['api_key'] ?? null;
        
        if (!$apiKey) {
            Log::warning('VirusTotal API key not configured, falling back to basic scan');
            return $this->basicSecurityScan($file);
        }

        try {
            // Upload file to VirusTotal
            $response = Http::attach(
                'file', file_get_contents($file->getPathname()), $file->getClientOriginalName()
            )->post('https://www.virustotal.com/vtapi/v2/file/scan', [
                'apikey' => $apiKey
            ]);

            if (!$response->successful()) {
                throw new \Exception('VirusTotal API request failed');
            }

            $result = $response->json();
            $scanId = $result['scan_id'] ?? null;

            if (!$scanId) {
                throw new \Exception('No scan ID received from VirusTotal');
            }

            // Wait a moment then get results
            sleep(2);
            
            $reportResponse = Http::get('https://www.virustotal.com/vtapi/v2/file/report', [
                'apikey' => $apiKey,
                'resource' => $scanId
            ]);

            $report = $reportResponse->json();
            $positives = $report['positives'] ?? 0;
            $total = $report['total'] ?? 0;

            return [
                'clean' => $positives === 0,
                'method' => 'virustotal',
                'positives' => $positives,
                'total' => $total,
                'scan_id' => $scanId,
                'scanned_at' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            Log::error('VirusTotal scan failed', ['error' => $e->getMessage()]);
            return $this->basicSecurityScan($file);
        }
    }

    /**
     * Scan with AWS GuardDuty (placeholder for AWS integration)
     */
    private function scanWithAWSGuardDuty(UploadedFile $file): array
    {
        // This would integrate with AWS GuardDuty Malware Protection
        // For now, fall back to basic scan
        Log::info('AWS GuardDuty scan not implemented, using basic scan');
        return $this->basicSecurityScan($file);
    }

    /**
     * Validate MIME type matches file extension
     */
    private function validateMimeTypeExtension(string $mimeType, string $extension): bool
    {
        $validCombinations = [
            'pdf' => ['application/pdf'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'txt' => ['text/plain'],
        ];

        return isset($validCombinations[$extension]) && 
               in_array($mimeType, $validCombinations[$extension]);
    }

    /**
     * Check for executable signatures in file content
     */
    private function containsExecutableSignatures(UploadedFile $file): bool
    {
        $handle = fopen($file->getPathname(), 'rb');
        $header = fread($handle, 1024); // Read first 1KB
        fclose($handle);

        // Check for common executable signatures
        $executableSignatures = [
            'MZ',      // Windows PE
            '\x7fELF', // Linux ELF
            'PK',      // ZIP (could contain executables)
            '#!/',     // Shell script
            '<?php',   // PHP script
        ];

        foreach ($executableSignatures as $signature) {
            if (strpos($header, $signature) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate file header matches expected format
     */
    private function validateFileHeader(UploadedFile $file): bool
    {
        $handle = fopen($file->getPathname(), 'rb');
        $header = fread($handle, 8);
        fclose($handle);

        $extension = strtolower($file->getClientOriginalExtension());
        
        $expectedHeaders = [
            'pdf' => '%PDF',
            'jpg' => "\xFF\xD8\xFF",
            'jpeg' => "\xFF\xD8\xFF",
            'png' => "\x89PNG\r\n\x1a\n",
            'gif' => 'GIF8',
        ];

        if (!isset($expectedHeaders[$extension])) {
            return true; // No specific header check for this type
        }

        return strpos($header, $expectedHeaders[$extension]) === 0;
    }
}