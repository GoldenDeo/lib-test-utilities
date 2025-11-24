# Library Test Utilities

[![CI](https://github.com/silpo-tech/lib-test-utilities/actions/workflows/ci.yml/badge.svg)](https://github.com/silpo-tech/lib-test-utilities/actions)
[![codecov](https://codecov.io/gh/silpo-tech/lib-test-utilities/graph/badge.svg)](https://codecov.io/gh/silpo-tech/lib-test-utilities)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

## About

Library Test Utilities contains additional test utilities for PHP projects.

## Installation

Require package and its dependencies with composer:

```bash
composer require silpo-tech/lib-test-utilities --dev
```

## Usage

### Comparator

```php
protected function setUp(): void
{
    Factory::getInstance()->register(
        new IgnoreDynamicFieldsComparator(classes: [Recurring::class], properties: [
            'id',
            'createdAt',
            'updatedAt',
        ]),
    );
}

protected function tearDown(): void
{
    parent::tearDown();
    Factory::getInstance()->reset();
}
```

## Validation Testing ##

### TestDataCorruptor ###

Manipulates nested array structures using dot notation for test data preparation.

```php
use SilpoTech\Lib\TestUtilities\Validation\TestDataCorruptor;

$data = [
    'user' => [
        'name' => 'John',
        'email' => 'john@example.com',
        'profile' => ['age' => 25]
    ]
];

// Set values using dot notation
$corrupted = TestDataCorruptor::setValue($data, 'user.email', 'invalid-email');
$corrupted = TestDataCorruptor::setValue($data, 'user.profile.age', -1);

// Remove values
$corrupted = TestDataCorruptor::removeValue($data, 'user.email');

// Set empty array
$corrupted = TestDataCorruptor::setEmptyArray($data, 'user.profile');
```

### ValidationTestHelper ###

Creates standardized validation test cases from valid data.

```php
use SilpoTech\Lib\TestUtilities\Validation\ValidationTestHelper;

$validData = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 25,
    'role' => 'user',
];

$helper = new ValidationTestHelper(
    validData: $validData,
    expectedStatusCode: 400 // Optional, defaults to 400
);

// Test required field
$testCase = $helper->required('Missing Name', 'name', 'validation.not_blank');

// Test enum validation
$testCase = $helper->enum('Invalid Role', 'role', 'invalid_role');

// Test length validation
$testCase = $helper->tooLong('Name Too Long', 'name', 50);
$testCase = $helper->tooShort('Name Too Short', 'name', 3);

// Test format validation
$testCase = $helper->invalidEmail('Invalid Email', 'email');
$testCase = $helper->invalidUrl('Invalid Website', 'website');
$testCase = $helper->invalidUuid('Invalid ID', 'id');

// Test custom validation
$testCase = $helper->invalid('Negative Age', 'age', -1, 'validation.positive');
```

**Complete example with PHPUnit:**

```php
use SilpoTech\Lib\TestUtilities\Validation\ValidationTestHelper;

class UserValidationTest extends TestCase
{
    /**
     * @dataProvider validationProvider
     */
    public function testValidation(
        string $name,
        int $expectedStatusCode,
        array $requestData,
        array $expectedValidationErrors
    ): void {
        $response = $this->client->post('/api/users', $requestData);

        $this->assertSame($expectedStatusCode, $response->getStatusCode());

        foreach ($expectedValidationErrors as $error) {
            $this->assertStringContainsString($error, $response->getContent());
        }
    }

    public static function validationProvider(): iterable
    {
        $validData = ['name' => 'John', 'email' => 'john@example.com'];
        $helper = new ValidationTestHelper($validData);

        yield 'Missing name' => $helper->required('Missing Name', 'name');
        yield 'Invalid email' => $helper->invalidEmail('Invalid Email', 'email');
        yield 'Name too short' => $helper->tooShort('Short Name', 'name', 3);
    }
}
```

## Development

### Tests

To run the test suite, you need to install the dependencies:

```bash
composer install
```

Run test suite:

```bash
composer test:run
```

Run test suite with coverage (requires pcov extension):

```bash
composer test:coverage
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.