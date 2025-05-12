# php-requestkit

**Lightweight and efficient PHP library for robust request data validation and transformation.**

Simplify your request processing with `php-requestkit`. This library allows you to define schemas for your incoming HTTP requests (both form submissions and JSON payloads) and effortlessly validate and transform the data. Ensure data integrity and streamline your application logic with schema-based validation, input sanitization, and flexible transformation methods.

## Key Features

* **Schema-based validation:** Define clear and concise validation rules for your request data.
* **Data transformation:**  Automatically transform and sanitize input data based on your schema.
* **Multiple data types:** Supports strings, integers, booleans, dates, date-times, and numeric types with various constraints.
* **Nested data and collections:** Validate complex data structures, including nested objects and arrays.
* **Error handling:** Provides detailed error messages for easy debugging and user feedback.
* **Extensible:**  Easily extend schemas and create reusable validation logic.

## Installation

```bash
composer require phpdevcommunity/php-requestkit
```

## Basic Usage

1.  **Create a Schema:** Define your data structure and validation rules using `Schema::create()` and `Type` classes.

```php
<?php

use PhpDevCommunity\RequestKit\Schema\Schema;
use PhpDevCommunity\RequestKit\Type;

$userSchema = Schema::create([
    'username' => Type::string()->length(5, 20)->required(),
    'email' => Type::email()->required(),
    'age' => Type::int()->min(18)->optional(), // Optional field
]);
```

2.  **Process Request Data:** Use the `process()` method of your schema to validate and transform incoming data.

```php
<?php

use PhpDevCommunity\RequestKit\Exceptions\InvalidDataException;

// ... (Schema creation from step 1) ...

$requestData = [
    'username' => 'john_doe',
    'email' => 'john.doe@example.com',
    'age' => '30', // Can be string, will be cast to int
];

try {
    $validatedData = $userSchema->process($requestData);
    // Access validated data as an array-like object
    $username = $validatedData->get('username');
    $email = $validatedData->get('email');
    $age = $validatedData->get('age');

    // ... continue processing with validated data ...

    print_r($validatedData->toArray()); // Output validated data as array

} catch (InvalidDataException $e) {
    // Handle validation errors
    $errors = $e->getErrors();
    print_r($errors);
}
```

## Usage Examples

### Validating REST API Request Body (JSON or Form Data)

This example demonstrates validating data from a REST API endpoint (e.g., POST, PUT, PATCH requests).

```php
<?php

namespace MonApi\Controller;

use PhpDevCommunity\RequestKit\Schema\Schema;
use PhpDevCommunity\RequestKit\Type;
use PhpDevCommunity\RequestKit\Exceptions\InvalidDataException;

class UserController
{
    public function createUser(array $requestData)
    {
        $schema = Schema::create([
            'user' => Type::item([
                'username' => Type::string()->length(5, 20)->required(),
                'email' => Type::email()->required(),
                'age' => Type::int()->min(18),
                'roles' => Type::arrayOf(Type::string())->required(),
                'metadata' => Type::map(Type::string())->required(),
                'address' => Type::item([
                    'street' => Type::string()->length(5, 100),
                    'city' => Type::string()->allowed('Paris', 'London'),
                ]),
            ]),
        ]);

        try {
            $validatedData = $schema->process($requestData);
            // Access validated data directly using dot notation
            $username = $validatedData->get('user.username');
            $email = $validatedData->get('user.email');
            $age = $validatedData->get('user.age');
            $roles = $validatedData->get('user.roles');
            $metadata = $validatedData->get('user.metadata'); // <-- map : Instance Of KeyValueObject
            $street = $validatedData->get('user.address.street');
            $city = $validatedData->get('user.address.city');

            // Process validated data (e.g., save to database)
            // ...
            return $validatedData; // Or return a JSON response
        } catch (InvalidDataException $e) {
            // Handle validation errors and return an appropriate error response
            http_response_code(400); // Bad Request
            return ['errors' => $e->getErrors()]; // Or return a JSON error response
        }
    }
}

// Usage example (assuming $requestData is the parsed request body)
$controller = new UserController();
$requestData = [
    'user' => [
        'username' => 'john_doe',
        'email' => 'john.doe@example.com',
        'age' => 30,
        'roles' => ['admin', 'user'],
        'metadata' => [
            'department' => 'IT',
            'level' => 'senior',
        ],
        'address' => [
            'street' => 'Main Street',
            'city' => 'London',
        ],
    ],
];

$result = $controller->createUser($requestData);
print_r($result);
```

### Validating URL Parameters (Query String)

Validate parameters passed in the URL's query string.

```php
<?php

namespace MonApi\Controller;

use PhpDevCommunity\RequestKit\Schema\Schema;
use PhpDevCommunity\RequestKit\Type;
use PhpDevCommunity\RequestKit\Exceptions\InvalidDataException;

class ProductController
{
    public function getProduct(array $urlParams)
    {
        $schema = Schema::create([
            'id' => Type::int()->required()->min(1),
            'category' => Type::string()->allowed('electronics', 'clothing')->required(),
            'page' => Type::int()->min(1)->default(1), // Optional with default value
        ]);

        try {
            $validatedParams = $schema->process($urlParams);
            $id = $validatedParams->get('id');
            $category = $validatedParams->get('category');
            $page = $validatedParams->get('page'); // Will be 1 if 'page' is not in $urlParams

            // Retrieve product using validated parameters
            // ...
            return $validatedParams; // Or return a JSON response
        } catch (InvalidDataException $e) {
            // Handle validation errors
            http_response_code(400); // Bad Request
            return ['errors' => $e->getErrors()]; // Or return a JSON error response
        }
    }
}

// Usage example (assuming $urlParams is extracted from $_GET)
$controller = new ProductController();
$urlParams = ['id' => 123, 'category' => 'electronics'];
$result = $controller->getProduct($urlParams);
print_r($result);
```

### Validating Collections of Data (Arrays)

Validate arrays of data, especially useful for batch operations or list endpoints.

```php
<?php

namespace MonApi\Controller;

use PhpDevCommunity\RequestKit\Schema\Schema;
use PhpDevCommunity\RequestKit\Type;
use PhpDevCommunity\RequestKit\Exceptions\InvalidDataException;

class OrderController
{
    public function createOrders(array $ordersData)
    {
        $orderSchema = Type::item([
            'product_id' => Type::int()->required(),
            'quantity' => Type::int()->required()->min(1),
        ]);

        $schema = Schema::create(['orders' => Type::arrayOf($orderSchema)->required()]);

        try {
            $validatedOrders = $schema->process($ordersData);

            $orders = $validatedOrders->get('orders'); // Array of validated order items
            $firstProductId = $validatedOrders->get('orders.0.product_id');

            // Process validated orders
            // ...
            return $validatedOrders; // Or return a JSON response
        } catch (InvalidDataException $e) {
            // Handle validation errors
            http_response_code(400); // Bad Request
            return ['errors' => $e->getErrors()]; // Or return a JSON error response
        }
    }
}

// Usage example (assuming $ordersData is the parsed request body)
$controller = new OrderController();
$ordersData = [
    'orders' => [
        ['product_id' => 1, 'quantity' => 2],
        ['product_id' => 2, 'quantity' => 1],
        ['product_id' => 'invalid', 'quantity' => 0], // Will trigger validation errors
    ],
];

$result = $controller->createOrders($ordersData);
print_r($result); // Will print error array if validation fails
```

## Error Handling with `InvalidDataException`

When validation fails, the `Schema::process()` method throws an `InvalidDataException`.  This exception provides methods to access detailed error information.

### Retrieving All Errors

Use `getErrors()` to get an associative array where keys are field paths and values are error messages.

```php
<?php
// ... inside a catch block for InvalidDataException ...

    } catch (InvalidDataException $e) {
        $errors = $e->getErrors();
        // $errors will be like:
        // [
        //    'orders.2.product_id' => 'Value must be an integer, got: string',
        //    'orders.2.quantity' => 'quantity must be at least 1',
        // ]
        return ['errors' => $errors];
    }
```

### Retrieving a Specific Error

Use `getError(string $key)` to get the error message for a specific field path. Returns `null` if no error exists for that path.

```php
<?php
// ... inside a catch block for InvalidDataException ...

    } catch (InvalidDataException $e) {
        $productIdError = $e->getError('orders.2.product_id');
        if ($productIdError) {
            // $productIdError will be: 'Value must be an integer, got: string'
            return ['errors' => ['product_id_error' => $productIdError]]; // Structure error response as needed
        }
    }
```

### Formatting Error Response with `toResponse()`

Use `toResponse()` to get a pre-formatted associative array suitable for returning as an API error response. This includes status, a general error message, and detailed validation errors.

```php
<?php
// ... inside a catch block for InvalidDataException ...

    } catch (InvalidDataException $e) {
        $response = $e->toResponse();
        // $response will be like:
        // [
        //     'status' => 'error',
        //     'message' => 'Validation failed',
        //     'errors' => [ /* ... detailed errors from getErrors() ... */ ],
        // ]
        http_response_code($response['code']); // Set appropriate HTTP status code (e.g., 400)
        return $response;
    }
```

### Accessing Exception `message` and `code`

`InvalidDataException` extends PHP's base `\Exception`, allowing access to standard exception properties.

```php
<?php
// ... inside a catch block for InvalidDataException ...

    } catch (InvalidDataException $e) {
        $message = $e->getMessage(); // General error message (e.g., "Validation failed")
        $code = $e->getCode();       //  Error code (you can customize this)

        return [
            'message' => $message,
            'code' => $code,
            'errors' => $e->getErrors(), // Detailed errors
        ];
    }
```

## Available Validation Types and Rules

`php-requestkit` provides a variety of built-in data types with a rich set of validation rules.

*   **`Type::string()`:**
    *   `required()`: Field is mandatory.
    *   `optional()`: Field is optional.
    *   `length(min, max)`:  String length constraints.
    *   `trim()`: Trim whitespace from both ends.
    *   `lowercase()`: Convert to lowercase.
    *   `uppercase()`: Convert to uppercase.
    *   `email()`: Validate email format.
    *   `allowed(...values)`:  Value must be one of the allowed values.
    *   `removeSpaces()`: Remove all spaces.
    *   `padLeft(length, char)`: Pad string to the left with a character.
    *   `removeChars(...chars)`: Remove specific characters.
    *   `strict()`: Strict type validation (only accepts strings).

*   **`Type::int()`:**
    *   `required()`, `optional()`, `strict()`: Same as StringType.
    *   `min(value)`: Minimum value.
    *   `max(value)`: Maximum value.

*   **`Type::bool()`:**
    *   `required()`, `optional()`, `strict()`: Same as StringType.

*   **`Type::date()` and `Type::datetime()`:**
    *   `required()`, `optional()`: Same as StringType.
    *   `format(format)`: Specify the date/datetime format (using PHP date formats).

*   **`Type::numeric()`:**
    *   `required()`, `optional()`: Same as StringType.  Validates any numeric value (integer or float).

*   **`Type::item(array $schema)`:** For nested objects/items. Defines a schema for a nested object within the main schema.

*   **`Type::arrayOf(Type $type)`:** For collections/arrays.  Defines that a field should be an array of items, each validated against the provided `Type`.
*   **`Type::map(Type $type)`:** For key-value objects (associative arrays). Defines that a field should be an object where each value is validated against the provided Type, and keys must be strings.
## Extending Schemas

You can extend existing schemas to reuse and build upon validation logic.

```php
<?php
use PhpDevCommunity\RequestKit\Schema\Schema;
use PhpDevCommunity\RequestKit\Type;

$baseUserSchema = Schema::create([
    'name' => Type::string()->required(),
    'email' => Type::email()->required(),
]);

$extendedUserSchema = $baseUserSchema->extend([
    'password' => Type::string()->length(8)->required(),
    'address' => Type::item([
        'city' => Type::string(),
        'zip' => Type::string()->length(5,10),
    ])
]);

// $extendedUserSchema now includes 'name', 'email', 'password', and 'address' fields
```
