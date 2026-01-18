# Malaika Backend API

A comprehensive Laravel-based REST API powered by API Platform that serves the Malaika Scholarship Platform. This system manages scholarship opportunities, student applications, user authentication, payment processing, content management, and various educational support services across multiple user roles.

## 🚀 Technology Stack

- **Framework**: Laravel 11.x with API Platform
- **API Framework**: API Platform for Laravel (automatic CRUD, documentation, filtering)
- **Admin Interface**: API Platform Admin (React-based admin panel)
- **Authentication**: Laravel Sanctum (Token-based JWT)
- **Database**: SQLite (development) / MySQL (production)
- **Cache**: Redis
- **Queue**: Redis/Database
- **File Storage**: AWS S3 (configurable)
- **Search**: Elasticsearch (for advanced search)
- **Payment**: Stripe, PayPal, Mobile Money APIs
- **Email**: Laravel Mail with SMTP/SES
- **Documentation**: Auto-generated OpenAPI/Swagger via API Platform

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ (for admin interface)
- SQLite (development) or MySQL (production)
- Redis (optional, for caching and queues)
- Elasticsearch (optional, for advanced search)

## 🛠️ Installation

### 1. Clone and Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies (for admin interface)
npm install
```

### 2. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database and other services in .env
```

### 3. Database Setup

```bash
# Run migrations to create all tables
php artisan migrate

# (Optional) Seed the database with sample data
php artisan db:seed
```

### 4. Publish Vendor Assets

```bash
# Publish API Platform assets (CSS, JS for documentation)
php artisan vendor:publish --provider="ApiPlatform\Laravel\ApiPlatformProvider"

# Create storage symlink for file uploads
php artisan storage:link
```

### 5. Start Development Servers

```bash
# Start Laravel development server
php artisan serve

# In another terminal, start the queue worker
php artisan queue:work

# (Optional) Start admin interface development server
npm run dev
```

## 📚 API Documentation

Once the server is running, access:

- **API Documentation**: http://localhost:8000/api/docs
- **API Entrypoint**: http://localhost:8000/api
- **Admin Interface**: http://localhost:3000/admin (after npm run dev)
- **Swagger UI**: Available through the API documentation

## 🏗️ System Architecture

### Core Components

1. **User Management**: Multi-role authentication (Students, Parents, Schools, Sponsors, Donors, Admins)
2. **Scholarship Opportunities**: Complete CRUD with advanced filtering and search
3. **Application Workflow**: Multi-step application process with document uploads
4. **Payment Processing**: Multiple gateway support (Stripe, PayPal, Mobile Money)
5. **Content Management**: Dynamic homepage content with flexible layouts
6. **Notification System**: Multi-channel notifications (Email, SMS, In-App)
7. **File Management**: Secure document upload and storage
8. **Analytics & Reporting**: Real-time metrics and dashboard analytics

### API Resources

The system provides RESTful endpoints for:

```
/api/users                    # User management
/api/opportunities           # Scholarship opportunities
/api/applications           # Student applications
/api/payments               # Payment processing
/api/notifications          # Notification management
/api/scholastic_materials   # Educational materials inventory
/api/documents              # Document management
/api/homepage_sections      # Dynamic homepage content
/api/content_blocks         # Content management
/api/layout_templates       # Layout templates
```

## 🎨 Content Management System

### Dynamic Homepage Features

- **Flexible Layouts**: JSON-based configuration for any layout design
- **Content Types**: Text, images, videos, HTML, external embeds, advertisements
- **User Spotlights**: Dynamic showcasing of students, sponsors, and schools
- **Scheduling**: Time-based content display with start/end dates
- **A/B Testing**: Traffic splitting and conversion tracking
- **Analytics**: Impression counts and click-through rates
- **Security**: XSS prevention and content validation

### Admin Interface

Access the admin panel at `http://localhost:3000/admin` to manage:

- User accounts and roles
- Scholarship opportunities
- Applications and reviews
- Payment transactions
- Homepage content and layouts
- System analytics and reports

## 🔧 Configuration

### Important Notes

- **`public/vendor` folder**: This contains published assets from API Platform (CSS, JS, images for documentation). It's automatically generated and should not be committed to the repository.
- **Regenerating assets**: If the API documentation doesn't load properly, run `php artisan vendor:publish --provider="ApiPlatform\Laravel\ApiPlatformProvider" --force`

### Environment Variables

Key configuration options in `.env`:

```env
# Database
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite

# Authentication
SANCTUM_STATEFUL_DOMAINS=localhost:3000

# File Storage
FILESYSTEM_DISK=local
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket

# Payment Gateways
STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_CLIENT_SECRET=your_paypal_secret

# Email
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password

# Cache & Queue
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Search
SCOUT_DRIVER=elasticsearch
ELASTICSEARCH_HOST=localhost:9200
```

## 🧪 Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run property-based tests (with coverage)
php artisan test --coverage
```

### Property-Based Testing

The system includes comprehensive property-based tests that validate:

- Authentication and authorization properties
- Data integrity across all operations
- Payment processing accuracy
- Content management security
- File upload and validation
- Notification delivery
- Search and filtering accuracy

## 📁 Project Structure

```
malaika-api/
├── app/
│   ├── Models/              # Eloquent models (API Resources)
│   │   ├── User.php
│   │   ├── Opportunity.php
│   │   ├── Application.php
│   │   ├── Payment.php
│   │   ├── HomepageSection.php
│   │   └── ContentBlock.php
│   ├── Http/
│   │   ├── Controllers/     # Custom controllers
│   │   ├── Middleware/      # Custom middleware
│   │   └── Requests/        # Form request validation
│   ├── Services/            # Business logic services
│   │   ├── PaymentService.php
│   │   ├── NotificationService.php
│   │   └── ContentSecurityService.php
│   └── Providers/           # Service providers
├── config/
│   ├── api-platform.php     # API Platform configuration
│   ├── sanctum.php          # Authentication configuration
│   ├── cors.php             # CORS configuration
│   └── filesystems.php      # File storage configuration
├── database/
│   ├── migrations/          # Database migrations
│   ├── seeders/             # Database seeders
│   └── factories/           # Model factories
├── tests/
│   ├── Feature/             # Feature tests
│   ├── Unit/                # Unit tests
│   └── Property/            # Property-based tests
├── storage/
│   ├── app/                 # File uploads
│   └── logs/                # Application logs
└── routes/
    ├── api.php              # API routes
    └── web.php              # Web routes
```

## 🚀 Deployment

### Production Setup

1. **Environment**: Set `APP_ENV=production` in `.env`
2. **Database**: Configure MySQL/PostgreSQL connection
3. **Cache**: Set up Redis for caching and queues
4. **Storage**: Configure AWS S3 for file storage
5. **Search**: Set up Elasticsearch cluster
6. **SSL**: Configure HTTPS and update CORS settings

### Optimization Commands

```bash
# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Clear caches (if needed)
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

## 🔒 Security Features

- **Authentication**: JWT token-based with configurable expiration
- **Authorization**: Role-based access control (RBAC)
- **Input Validation**: Comprehensive validation for all endpoints
- **XSS Prevention**: HTML sanitization for content management
- **File Security**: Virus scanning and type validation
- **Rate Limiting**: API rate limiting to prevent abuse
- **Audit Logging**: Security event logging for compliance

## 📊 Monitoring & Analytics

- **Real-time Metrics**: Application, user, and system metrics
- **Performance Tracking**: API response times and throughput
- **Error Monitoring**: Comprehensive error logging and alerting
- **Business Analytics**: Scholarship success rates, user engagement
- **Content Analytics**: Homepage content performance and A/B testing results

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support and questions:

- Check the API documentation at `/api/docs`
- Review the admin interface at `/admin`
- Check application logs in `storage/logs/`
- Contact the development team

---

**Built with ❤️ for educational empowerment through the Malaika Scholarship Platform**