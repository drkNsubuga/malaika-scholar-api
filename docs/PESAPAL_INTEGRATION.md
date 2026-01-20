# Pesapal Payment Integration

This document explains how to set up and use the Pesapal payment integration in the Malaika Backend API.

## Overview

The Malaika platform integrates with [Pesapal](https://pesapal.com) to process payments for:
- Scholarship support payments
- Material donations
- General donations
- Application fees
- Promotion fees

## Features

- **Secure Payment Processing**: Full integration with Pesapal API v3
- **Multiple Payment Methods**: Cards, Mobile Money (M-Pesa, Airtel Money), Bank transfers
- **Real-time Status Updates**: Instant Payment Notifications (IPN) for status updates
- **Refund Support**: Process refunds through Pesapal
- **Comprehensive Logging**: Full audit trail of all payment activities
- **Rate Limiting**: Protection against payment abuse
- **Multi-currency Support**: KES, USD, EUR, GBP

## Setup Instructions

### 1. Get Pesapal Credentials

1. Visit [Pesapal Developer Portal](https://developer.pesapal.com/)
2. Create an account and get your API credentials
3. Note down your Consumer Key and Consumer Secret

### 2. Environment Configuration

Copy the main environment file and configure your credentials:

```bash
cp .env.example .env
```

Add the following to your `.env` file:

```env
# Pesapal Configuration
PESAPAL_ENVIRONMENT=sandbox  # Use 'live' for production
PESAPAL_CONSUMER_KEY=your_consumer_key_here
PESAPAL_CONSUMER_SECRET=your_consumer_secret_here
PESAPAL_CALLBACK_URL="${APP_URL}/api/payments/pesapal/callback"
PESAPAL_IPN_URL="${APP_URL}/api/payments/pesapal/ipn"
PESAPAL_DEFAULT_CURRENCY=KES
```

### 3. Register IPN URL

Register your IPN URL with Pesapal to receive payment notifications:

```bash
php artisan pesapal:register-ipn
```

This will return an IPN ID that you should add to your `.env` file:

```env
PESAPAL_DEFAULT_NOTIFICATION_ID=your_ipn_id_here
```

### 4. Test Connection

Test your Pesapal integration:

```bash
php artisan pesapal:test-connection
```

## API Endpoints

### Initiate Payment

**POST** `/api/payments/initiate`

Initiate a new payment with Pesapal.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "amount": 1000,
    "currency": "KES",
    "description": "Scholarship support payment",
    "payment_type": "Scholarship Support",
    "payable_type": "App\\Models\\Application",
    "payable_id": 123,
    "recipient_id": 456,
    "address_line_1": "123 Main Street",
    "city": "Nairobi",
    "country_code": "KE"
}
```

**Response:**
```json
{
    "success": true,
    "payment_id": 789,
    "order_tracking_id": "abc123def456",
    "redirect_url": "https://cybqa.pesapal.com/pesapalv3/api/Transactions/SubmitOrderRequest?OrderTrackingId=abc123def456",
    "merchant_reference": "MALAIKA_1642678901_xyz789",
    "message": "Payment initiated successfully. Please complete payment on Pesapal."
}
```

### Check Payment Status

**GET** `/api/payments/{payment}/status`

Check the current status of a payment.

**Response:**
```json
{
    "success": true,
    "payment": {
        "id": 789,
        "status": "Completed",
        "amount": "1000.00",
        "currency": "KES",
        "formatted_amount": "KSh 1,000.00",
        "description": "Scholarship support payment",
        "created_at": "2024-01-20T10:30:00Z",
        "processed_at": "2024-01-20T10:35:00Z"
    }
}
```

### Payment History

**GET** `/api/payments/history`

Get payment history for the authenticated user.

**Query Parameters:**
- `per_page` (optional): Number of payments per page (default: 15)

**Response:**
```json
{
    "success": true,
    "payments": {
        "data": [...],
        "current_page": 1,
        "total": 25,
        "per_page": 15
    }
}
```

### Process Refund (Admin Only)

**POST** `/api/payments/{payment}/refund`

Process a refund for a completed payment.

**Request Body:**
```json
{
    "amount": 500.00,
    "reason": "Partial refund requested by user"
}
```

## Payment Flow

1. **Initiate Payment**: Frontend calls `/api/payments/initiate`
2. **Redirect to Pesapal**: User is redirected to Pesapal payment page
3. **Complete Payment**: User completes payment on Pesapal
4. **Callback**: Pesapal redirects back to your callback URL
5. **IPN Notification**: Pesapal sends payment status via IPN
6. **Status Update**: Payment status is updated in database

## Payment Statuses

- **Pending**: Payment initiated but not completed
- **Completed**: Payment successfully processed
- **Failed**: Payment failed or was declined
- **Cancelled**: Payment was cancelled by user
- **Refunded**: Payment was fully refunded
- **Partially Refunded**: Payment was partially refunded

## Security Features

### Rate Limiting
- 10 payment attempts per minute per user
- 100 payment attempts per hour per user
- 500 payment attempts per day per user

### Access Control
- Users can only access their own payments
- Recipients can access payments made to them
- Admins can access all payments
- Only admins can process refunds

### Data Protection
- Sensitive gateway response data is hidden from API responses
- All payment activities are logged for audit purposes
- Secure token-based authentication required

## Error Handling

The API returns standardized error responses:

```json
{
    "success": false,
    "error": "Payment initiation failed: Invalid amount"
}
```

Common error scenarios:
- Invalid payment amount
- Missing required fields
- Authentication failures
- Pesapal API errors
- Network connectivity issues

## Testing

### Sandbox Environment

Use the sandbox environment for testing:
- Set `PESAPAL_ENVIRONMENT=sandbox`
- Use test credentials from Pesapal developer dashboard
- Test payments won't charge real money

### Test Cards

Pesapal provides test card numbers for different scenarios:
- **Successful Payment**: 4000000000000002
- **Failed Payment**: 4000000000000010
- **Insufficient Funds**: 4000000000000019

## Monitoring and Logging

### Payment Logs

All payment activities are logged with the following information:
- User ID and payment details
- Pesapal responses and status changes
- Error messages and stack traces
- IP addresses and timestamps

### Log Locations

- Application logs: `storage/logs/laravel.log`
- Payment-specific logs are tagged with `payment` context

### Monitoring Commands

```bash
# Test Pesapal connectivity
php artisan pesapal:test-connection

# Register new IPN URL
php artisan pesapal:register-ipn --url=https://yourdomain.com/api/payments/pesapal/ipn

# Check payment gateway status
php artisan queue:work  # Process payment notifications
```

## Troubleshooting

### Common Issues

1. **Authentication Failed**
   - Check consumer key and secret
   - Verify environment setting (sandbox/live)
   - Ensure credentials match the environment

2. **IPN Not Working**
   - Verify IPN URL is publicly accessible
   - Check notification ID is correctly set
   - Ensure IPN endpoint returns 200 status

3. **Callback Issues**
   - Verify callback URL is publicly accessible
   - Check for HTTPS requirement in production
   - Ensure proper CORS configuration

4. **Payment Status Not Updating**
   - Check IPN configuration
   - Verify queue workers are running
   - Check application logs for errors

### Debug Mode

Enable debug logging by setting:
```env
PESAPAL_LOG_REQUESTS=true
PESAPAL_LOG_RESPONSES=true
LOG_LEVEL=debug
```

## Production Deployment

### Pre-deployment Checklist

- [ ] Update environment to `PESAPAL_ENVIRONMENT=live`
- [ ] Use production Pesapal credentials
- [ ] Register production IPN URL
- [ ] Test payment flow end-to-end
- [ ] Configure proper SSL certificates
- [ ] Set up monitoring and alerting
- [ ] Configure queue workers for IPN processing

### Security Considerations

- Use HTTPS for all payment-related URLs
- Implement proper CORS policies
- Set up rate limiting and DDoS protection
- Regular security audits of payment flows
- Monitor for suspicious payment patterns

## Support

For technical support:
- Check the [Pesapal Developer Documentation](https://developer.pesapal.com/)
- Review application logs for error details
- Contact Pesapal support for API-related issues
- Use the test connection command for diagnostics

## API Reference

For complete API documentation, visit `/api/` when the application is running to see the auto-generated OpenAPI documentation.