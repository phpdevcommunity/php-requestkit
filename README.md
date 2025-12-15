> ⚠️ **Abandoned package**
>
> This package is abandoned and no longer maintained.  
> The author suggests using **[michel/requestkit](https://github.com/michelphp/requestkit)** instead.

# php-requestkit

**Lightweight and efficient PHP library for robust request data validation and transformation.**

Simplify your request processing with `php-requestkit`. This library allows you to define schemas for your incoming HTTP requests (both form submissions and JSON payloads) and effortlessly validate and transform the data. Ensure data integrity and streamline your application logic with schema-based validation, input sanitization, and flexible transformation methods.

## Key Features

* **Schema-based validation:** Define clear and concise validation rules for your request data.
* **Data transformation:**  Automatically transform and sanitize input data based on your schema.
* **HTTP Header Validation:** Define rules to validate incoming request headers.
* **Form & CSRF Processing:** Securely process form submissions with built-in CSRF token validation.
* **Internationalization (i18n):** Error messages can be easily translated. Comes with English and French built-in.
* **Multiple data types:** Supports strings, integers, booleans, dates, date-times, and numeric types with various constraints.
* **Nested data and collections:** Validate complex data structures, including nested objects and arrays.
* **Error handling:** Provides detailed error messages for easy debugging and user feedback.
* **Extensible:**  Easily extend schemas and create reusable validation logic.

## Installation

```bash
composer require depo/requestkit
```

## Basic Usage

1.  **Create a Schema:** Define your data structure and validation rules using `Schema::create()` and `Type` classes.

```php
<?php

use Depo\RequestKit\Schema\Schema;
use Depo\RequestKit\Type;

$userSchema = Schema::create([
    'username' => Type::string()->length(5, 20)->required(),
    'email' => Type::email()->required(),
    'age' => Type::int()->min(18)->optional(), // Optional field
]);
```

2.  **Process Request Data:** Use the `process()` method of your schema to validate and transform incoming data.

```php
<?php

use Depo\RequestKit\Exceptions\InvalidDataException;

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

use Depo\RequestKit\Schema\Schema;
use Depo\RequestKit\Type;
use Depo\RequestKit\Exceptions\InvalidDataException;

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

use Depo\RequestKit\Schema\Schema;
use Depo\RequestKit\Type;
use Depo\RequestKit\Exceptions\InvalidDataException;

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

use Depo\RequestKit\Schema\Schema;
use Depo\RequestKit\Type;
use Depo\RequestKit\Exceptions\InvalidDataException;

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

## Advanced Usage: HTTP Requests, Headers, and Forms

While `process()` is great for arrays, the library shines when working with PSR-7 `ServerRequestInterface` objects, allowing you to validate headers, form data, and CSRF tokens seamlessly.

### Validating Request Headers with `withHeaders()`

You can enforce rules on incoming HTTP headers by chaining the `withHeaders()` method to your schema. This is perfect for validating API keys, content types, or custom headers.

```php
<?php
use Depo\RequestKit\Schema\Schema;
use Depo\RequestKit\Type;
use Psr\Http\Message\ServerRequestInterface;

// Assume $request is a PSR-7 ServerRequestInterface object from your framework

$schema = Schema::create([
    'name' => Type::string()->required(),
])->withHeaders([
    'Content-Type' => Type::string()->equals('application/json'),
    'X-Api-Key' => Type::string()->required()->length(16),
]);

try {
    // processHttpRequest validates both headers and the request body
    $validatedData = $schema->processHttpRequest($request);
    $name = $validatedData->get('name');
    
} catch (InvalidDataException $e) {
    // Throws an exception if headers or body are invalid
    // e.g., if 'X-Api-Key' is missing or 'Content-Type' is not 'application/json'
    http_response_code(400);
    return ['errors' => $e->getErrors()];
}
```

### Processing Forms with CSRF Protection using `processForm()`

Securely handle form submissions (`application/x-www-form-urlencoded`) with optional CSRF token validation.

**1. Form with CSRF Validation (Recommended)**

Pass the expected CSRF token (e.g., from the user's session) as the second argument. The library will ensure the token is present and matches.

```php
<?php
use Depo\RequestKit\Schema\Schema;
use Depo\RequestKit\Type;
use Psr\Http\Message\ServerRequestInterface;

// Assume $request is a PSR-7 ServerRequestInterface object
// and $_SESSION['csrf_token'] holds the expected token.

$schema = Schema::create([
    'username' => Type::string()->required(),
    'comment' => Type::string()->required(),
]);

$expectedToken = $_SESSION['csrf_token'] ?? null;

try {
    // processForm validates the form body and the CSRF token
    // The third argument '_csrf' is the name of the form field containing the token.
    $validatedData = $schema->processFormHttpRequest($request, $expectedToken, '_csrf');
    // The '_csrf' field is automatically removed from the validated data.
    
} catch (InvalidDataException $e) {
    // Throws an exception if form data is invalid or CSRF token is missing/incorrect
    http_response_code(400);
    if ($e->getMessage() === 'Invalid CSRF token.') {
        http_response_code(403); // Forbidden
    }
    return ['errors' => $e->getErrors()];
}
```

**2. Form without CSRF Validation**

If you don't need CSRF protection for a specific form (e.g., a public search form), simply omit the second argument (or pass `null`).

```php
<?php
// ...
try {
    // No CSRF token is expected or validated
    $validatedData = $schema->processFormHttpRequest($request);
    
} catch (InvalidDataException $e) {
    // ...
}
```

## Error Handling with `InvalidDataException`

When validation fails, an `InvalidDataException` is thrown. This exception provides methods to access detailed error information.

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

## Available Validation Types and Rules

`php-requestkit` provides a variety of built-in data types with a rich set of validation rules.

*   **General Rules (Available for most types):**
    *   `required()`: Field is mandatory.
    *   `optional()`: Field is optional.
    *   `strict()`: Strict type validation (e.g., `Type::int()->strict()` will not accept `"123"`).

*   **`Type::string()`:**
    *   `length(min, max)`:  String length constraints.
    *   `trim()`: Trim whitespace from both ends.
    *   `lowercase()`: Convert to lowercase.
    *   `uppercase()`: Convert to uppercase.
    *   `email()`: Validate email format.
    *   `allowed(...values)`:  Value must be one of the allowed values.
    *   `removeSpaces()`: Remove all spaces.
    *   `padLeft(length, char)`: Pad string to the left with a character.
    *   `removeChars(...chars)`: Remove specific characters.
    *   `equals(value)`: The final value must be strictly equal to the given `value`.

*   **`Type::int()` & `Type::float()`:**
    *   `min(value)`: Minimum value.
    *   `max(value)`: Maximum value.
    *   `equals(value)`: The final value must be strictly equal to the given `value`.


*   **`Type::bool()`:**
    *   Accepts `true`, `false`, `'true'`, `'false'`, `1`, `0`, `'1'`, `'0'`.
    *   `equals(value)`: The final value must be strictly equal to the given `value`.

*   **`Type::date()` and `Type::datetime()`:**
    *   `format(format)`: Specify the date/datetime format (using PHP date formats).

*   **`Type::numeric()`:**
    *   Validates any numeric value (integer or float).
    *   `equals(value)`: The final value must be strictly equal to the given `value`.

*   **`Type::item(array $schema)`:** For nested objects/items. Defines a schema for a nested object within the main schema.

*   **`Type::arrayOf(Type $type)`:** For collections/arrays.  Defines that a field should be an array of items, each validated against the provided `Type`.
*   **`Type::map(Type $type)`:** For key-value objects (associative arrays). Defines that a field should be an object where each value is validated against the provided Type, and keys must be strings.

## Internationalization (i18n)

All error messages are translatable. The library includes English (`en`) and French (`fr`) by default.

### Changing the Language

To switch the language for all subsequent error messages, use the static method `Locale::setLocale()` at the beginning of your application.

```php
use Depo\RequestKit\Locale;

// Set the active language to French
Locale::setLocale('fr');
```

### Adding a New Language

You can easily add support for a new language using `Locale::addMessages()`. Provide the two-letter language code and an associative array of translations. If a key is missing for the active language, the library will automatically fall back to the English version.

Here is a full template for creating a new translation. You only need to translate the values.

```php
use Depo\RequestKit\Locale;

Locale::addMessages('en', [ // Example for English
    'error' => [
        'required' => 'Value is required, but got null or empty string.',
        'equals' => 'The value does not match the expected value.',
        'csrf' => 'Invalid CSRF token.',
        'json' => 'Invalid JSON input: {error}',
        'type' => [
            'string' => 'Value must be a string, got: {type}.',
            'int' => 'Value must be an integer, got: {type}.',
            'float' => 'Value must be a float, got: {type}.',
            'bool' => 'Value must be a boolean, got: {type}.',
            'numeric' => 'Value must be numeric, got: {type}.',
            'date' => 'Value must be a valid date.',
            'datetime' => 'Value must be a valid datetime.',
            'array' => 'Value must be an array.',
        ],
        'string' => [
            'min_length' => 'Value must be at least {min} characters long.',
            'max_length' => 'Value cannot be longer than {max} characters.',
            'email' => 'Value must be a valid email address.',
            'allowed' => 'Value is not allowed.',
        ],
        'int' => [
            'min' => 'Value must be at least {min}.',
            'max' => 'Value must be no more than {max}.',
        ],
        'array' => [
            'min_items' => 'Value must have at least {min} item(s).',
            'max_items' => 'Value must have at most {max} item(s).',
            'integer_keys' => 'All keys must be integers.',
        ],
        'map' => [
            'string_key' => 'Key "{key}" must be a string, got {type}.',
        ]
    ],
]);
```

## Extending Schemas

You can extend existing schemas to reuse and build upon validation logic.

```php
<?php
use Depo\RequestKit\Schema\Schema;
use Depo\RequestKit\Type;

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
