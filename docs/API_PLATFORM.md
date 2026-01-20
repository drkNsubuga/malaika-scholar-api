# API Platform Guide

This guide explains how to work with API Platform resources in the Malaika Backend API.

## Overview

API Platform automatically generates RESTful endpoints, documentation, and admin interfaces from your Eloquent models. This significantly reduces development time while providing powerful features out of the box.

## Key Features

- **Automatic CRUD Operations**: GET, POST, PUT, DELETE endpoints
- **Built-in Filtering**: Search, exact match, range filters
- **Pagination**: Configurable pagination with metadata
- **Sorting**: Multi-field sorting capabilities
- **Security**: Role-based access control
- **Documentation**: Auto-generated OpenAPI/Swagger docs
- **Admin Interface**: React-based admin panel

## API Resource Configuration

Models are configured as API resources using PHP attributes:

```php
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(),
        new Delete()
    ],
    middleware: ['auth:sanctum'],
    paginationItemsPerPage: 20
)]
class Opportunity extends Model
{
    // Model implementation
}
```

## Available Endpoints

All API resources follow RESTful conventions:

- `GET /api/{resource}` - List all items (with pagination)
- `POST /api/{resource}` - Create new item
- `GET /api/{resource}/{id}` - Get specific item
- `PUT /api/{resource}/{id}` - Update item
- `DELETE /api/{resource}/{id}` - Delete item

## Filtering and Search

Use query parameters for filtering:

```
GET /api/opportunities?school_name=University&category=Academic
GET /api/applications?status=Pending&order[created_at]=desc
GET /api/users?name=john&page=2
```

## Security

Access control is enforced at the resource level:

```php
#[ApiResource(
    operations: [
        new Get(security: "is_granted('ROLE_USER') and object.user == user"),
        new GetCollection(security: "is_granted('ROLE_USER')"),
        // ...
    ]
)]
```

## Custom Operations

Add custom business logic operations:

```php
new Post(
    uriTemplate: '/applications/{id}/submit',
    controller: SubmitApplicationController::class,
    name: 'submit_application'
)
```

## Admin Interface

Access the admin panel at `/admin` to manage all resources through a user-friendly interface.

For more details, see the [API Platform documentation](https://api-platform.com/docs/).