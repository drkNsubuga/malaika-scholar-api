# Security Guide

This document outlines the security features and best practices implemented in the Malaika Backend API.

## Authentication & Authorization

### JWT Token Authentication
- Laravel Sanctum provides secure token-based authentication
- Tokens expire after configurable time periods
- Refresh token mechanism for seamless user experience

### Role-Based Access Control (RBAC)
- Multiple user roles: Student/Parent, School, Sponsor, Donor, Admin
- Granular permissions for each endpoint
- Resource-level access control

## File Upload Security

### Multi-Layer Validation
1. **File Type Validation**: MIME type and extension checking
2. **File Size Limits**: Configurable per document type
3. **Content Scanning**: Virus scanning with multiple engines
4. **Header Validation**: Magic byte verification

### Virus Scanning Options
- **Basic**: File type and content validation
- **ClamAV**: Open-source antivirus integration
- **VirusTotal**: Cloud-based scanning service
- **AWS GuardDuty**: Enterprise malware protection

### Secure File Access
- Signed URLs with time-limited access
- One-time use tokens for sensitive documents
- IP address validation (optional)
- Comprehensive audit logging

## Payment Security

### Pesapal Integration
- Secure API v3 integration with OAuth 2.0
- Encrypted transaction data
- PCI DSS compliant payment processing
- Webhook signature verification

### Transaction Security
- Idempotent payment processing
- Comprehensive audit trails
- Refund protection and validation
- Rate limiting on payment attempts

## Rate Limiting

### API Rate Limits
- General API: 60 requests per minute per user
- Authentication: 5 attempts per minute per IP
- File uploads: 10 uploads per minute per user

### Payment Rate Limits
- 10 payment attempts per minute per user
- 100 payment attempts per hour per user
- 500 payment attempts per day per user

## Input Validation & XSS Prevention

### Request Validation
- Laravel Form Requests for all endpoints
- Comprehensive validation rules
- SQL injection prevention through Eloquent ORM

### Content Security
- HTML sanitization for user-generated content
- XSS prevention in content management
- CSP headers for file serving
- External embed validation

## Data Protection

### Sensitive Data Handling
- Password hashing with bcrypt
- Sensitive fields hidden from API responses
- Encrypted storage for payment data
- GDPR compliance features

### Audit Logging
- All security events logged
- User access tracking
- Payment transaction logs
- File access audit trails

## Security Headers

```php
// Applied to all responses
'X-Content-Type-Options' => 'nosniff',
'X-Frame-Options' => 'DENY',
'X-XSS-Protection' => '1; mode=block',
'Referrer-Policy' => 'strict-origin-when-cross-origin',
'Content-Security-Policy' => "default-src 'self'"
```

## Environment Security

### Production Checklist
- [ ] Set `APP_ENV=production`
- [ ] Use strong `APP_KEY`
- [ ] Configure HTTPS only
- [ ] Set secure session cookies
- [ ] Enable CSRF protection
- [ ] Configure proper CORS policies
- [ ] Set up rate limiting
- [ ] Enable audit logging
- [ ] Configure backup encryption

### Monitoring & Alerting
- Failed authentication attempts
- Suspicious file uploads
- Payment anomalies
- Rate limit violations
- Security event notifications

## Best Practices

1. **Regular Updates**: Keep dependencies updated
2. **Security Audits**: Regular penetration testing
3. **Access Reviews**: Periodic user access reviews
4. **Backup Security**: Encrypted backups with rotation
5. **Incident Response**: Documented security procedures

For security issues, contact the security team immediately.