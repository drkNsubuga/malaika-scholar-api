<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class FileUploadRateLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $userId = Auth::id();
        $config = config('security.rate_limiting');
        
        // Check rate limits
        if (!$this->checkRateLimit($userId, 'minute', $config['uploads_per_minute'] ?? 10)) {
            return response()->json([
                'error' => 'Too many upload attempts. Please wait a moment and try again.',
                'retry_after' => 60
            ], 429);
        }

        if (!$this->checkRateLimit($userId, 'hour', $config['uploads_per_hour'] ?? 100)) {
            return response()->json([
                'error' => 'Hourly upload limit exceeded. Please try again later.',
                'retry_after' => 3600
            ], 429);
        }

        if (!$this->checkRateLimit($userId, 'day', $config['uploads_per_day'] ?? 500)) {
            return response()->json([
                'error' => 'Daily upload limit exceeded. Please try again tomorrow.',
                'retry_after' => 86400
            ], 429);
        }

        // Increment counters
        $this->incrementCounter($userId, 'minute', 60);
        $this->incrementCounter($userId, 'hour', 3600);
        $this->incrementCounter($userId, 'day', 86400);

        return $next($request);
    }

    /**
     * Check if user is within rate limit
     */
    private function checkRateLimit(int $userId, string $period, int $limit): bool
    {
        $key = "upload_rate_limit:{$userId}:{$period}";
        $current = Cache::get($key, 0);
        
        return $current < $limit;
    }

    /**
     * Increment rate limit counter
     */
    private function incrementCounter(int $userId, string $period, int $ttl): void
    {
        $key = "upload_rate_limit:{$userId}:{$period}";
        
        if (Cache::has($key)) {
            Cache::increment($key);
        } else {
            Cache::put($key, 1, $ttl);
        }
    }
}