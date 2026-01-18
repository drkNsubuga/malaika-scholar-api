<?php

/*
 * This file is part of the API Platform project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

use ApiPlatform\Metadata\UrlGeneratorInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\Serializer\NameConverter\SnakeCaseToCamelCaseNameConverter;

return [
    'title' => 'Malaika Scholarship Platform API',
    'description' => 'A comprehensive API for managing scholarship opportunities, applications, and educational support services.',
    'version' => '1.0.0',
    'show_webby' => true,

    'routes' => [
        'domain' => null,
        // Global middleware applied to every API Platform routes
        // 'middleware' => [],
    ],

    'resources' => [
        app_path('Models'),
    ],

    'formats' => [
        'jsonld' => ['application/ld+json'],
        'json' => ['application/json'],
        'html' => ['text/html'],
    ],

    'patch_formats' => [
        'json' => ['application/merge-patch+json'],
    ],

    'docs_formats' => [
        'jsonld' => ['application/ld+json'],
        'json' => ['application/json'],
        'html' => ['text/html'],
    ],

    'error_formats' => [
        'jsonproblem' => ['application/problem+json'],
    ],

    'defaults' => [
        'pagination_enabled' => true,
        'pagination_partial' => false,
        'pagination_client_enabled' => true,
        'pagination_client_items_per_page' => true,
        'pagination_client_partial' => false,
        'pagination_items_per_page' => 20,
        'pagination_maximum_items_per_page' => 100,
        'route_prefix' => '/api',
        'middleware' => [],
    ],

    'pagination' => [
        'page_parameter_name' => 'page',
        'enabled_parameter_name' => 'pagination',
        'items_per_page_parameter_name' => 'itemsPerPage',
        'partial_parameter_name' => 'partial',
    ],

    'graphql' => [
        'enabled' => false,
        'nesting_separator' => '__',
        'introspection' => ['enabled' => true],
        'max_query_complexity' => 500,
        'max_query_depth' => 200,
        // 'middleware' => null,
    ],

    'graphiql' => [
        // 'enabled' => true,
        // 'domain' => null,
        // 'middleware' => null,
    ],

    // set to null if you want to keep snake_case
    'name_converter' => SnakeCaseToCamelCaseNameConverter::class,

    'exception_to_status' => [
        AuthenticationException::class => 401,
        AuthorizationException::class => 403,
    ],

    'swagger_ui' => [
        'enabled' => true,
        'apiKeys' => [
            'Bearer' => [
                'name' => 'Authorization',
                'type' => 'header',
            ],
        ],
    ],

    // 'openapi' => [
    //     'tags' => [],
    // ],

    'url_generation_strategy' => UrlGeneratorInterface::ABS_PATH,

    'serializer' => [
        'hydra_prefix' => false,
        // 'datetime_format' => \DateTimeInterface::RFC3339,
    ],

    // we recommend using "file" or "acpu"
    'cache' => 'file',

    // install `api-platform/http-cache`
    // 'http_cache' => [
    //     'etag' => false,
    //     'max_age' => null,
    //     'shared_max_age' => null,
    //     'vary' => null,
    //     'public' => null,
    //     'stale_while_revalidate' => null,
    //     'stale_if_error' => null,
    //     'invalidation' => [
    //         'urls' => [],
    //         'scoped_clients' => [],
    //         'max_header_length' => 7500,
    //         'request_options' => [],
    //         'purger' => ApiPlatform\HttpCache\SouinPurger::class,
    //     ],
    // ],
];
