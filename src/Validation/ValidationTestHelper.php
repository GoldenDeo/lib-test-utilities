<?php

declare(strict_types=1);

namespace SilpoTech\Lib\TestUtilities\Validation;

/**
 * Creates validation test cases by corrupting valid data in predictable ways.
 *
 * This helper simplifies the creation of validation test cases by:
 * - Starting with valid data
 * - Applying specific corruptions (invalid values, missing fields, etc.)
 * - Generating standardized test case structures
 *
 * Example usage:
 * ```php
 * $validData = ['name' => 'John', 'email' => 'john@example.com'];
 * $helper = new ValidationTestHelper($validData, expectedStatusCode: 400);
 *
 * $testCase = $helper->required('Missing Name', 'name', 'validation.not_blank');
 * // Returns: [
 * //   'name' => 'Missing Name',
 * //   'expectedStatusCode' => 400,
 * //   'requestData' => ['email' => 'john@example.com'],
 * //   'expectedValidationErrors' => ['validation.not_blank']
 * // ]
 * ```
 */
readonly class ValidationTestHelper
{
    /**
     * @param array $validData          Base valid data to corrupt for test cases
     * @param int   $expectedStatusCode Expected HTTP status code for validation errors (default: 400)
     */
    public function __construct(
        private array $validData,
        private int $expectedStatusCode = 400,
    ) {
    }

    /**
     * Creates a standardized test case structure.
     */
    private function createTestCase(
        string $name,
        array $requestData,
        array $expectedValidationErrors,
    ): array {
        return [
            'name' => $name,
            'expectedStatusCode' => $this->expectedStatusCode,
            'requestData' => $requestData,
            'expectedValidationErrors' => $expectedValidationErrors,
        ];
    }

    /**
     * Tests enum validation by setting an invalid choice value.
     *
     * @param string $name         Test case name
     * @param string $fieldPath    Dot-notation path to the field (e.g., 'user.role')
     * @param string $invalidValue Invalid enum value
     * @param string $errorCode    Expected validation error code (default: 'validation.choice')
     */
    public function enum(
        string $name,
        string $fieldPath,
        string $invalidValue,
        string $errorCode = 'validation.choice',
    ): array {
        return $this->createTestCase(
            name: $name,
            requestData: TestDataCorruptor::setValue(
                data: $this->validData,
                path: $fieldPath,
                value: $invalidValue,
            ),
            expectedValidationErrors: [$errorCode],
        );
    }

    /**
     * Tests required field validation by removing a field.
     *
     * @param string $name      Test case name
     * @param string $fieldPath Dot-notation path to the field (e.g., 'user.name')
     * @param string $errorCode Expected validation error code (default: 'validation.not_null')
     */
    public function required(
        string $name,
        string $fieldPath,
        string $errorCode = 'validation.not_null',
    ): array {
        return $this->createTestCase(
            name: $name,
            requestData: TestDataCorruptor::removeValue(
                data: $this->validData,
                path: $fieldPath,
            ),
            expectedValidationErrors: [$errorCode],
        );
    }

    /**
     * Tests max length validation by setting a value that exceeds the limit.
     *
     * @param string $name      Test case name
     * @param string $fieldPath Dot-notation path to the field
     * @param int    $maxLength Maximum allowed length
     * @param string $errorCode Expected validation error code (default: 'validation.length.max')
     */
    public function tooLong(
        string $name,
        string $fieldPath,
        int $maxLength,
        string $errorCode = 'validation.length.max',
    ): array {
        $value = str_repeat('x', $maxLength + 10);

        return $this->createTestCase(
            name: $name,
            requestData: TestDataCorruptor::setValue(
                data: $this->validData,
                path: $fieldPath,
                value: $value,
            ),
            expectedValidationErrors: [$errorCode],
        );
    }

    /**
     * Tests min length validation by setting a value that is too short.
     *
     * @param string $name      Test case name
     * @param string $fieldPath Dot-notation path to the field
     * @param int    $minLength Minimum required length
     * @param string $errorCode Expected validation error code (default: 'validation.length.min')
     */
    public function tooShort(
        string $name,
        string $fieldPath,
        int $minLength,
        string $errorCode = 'validation.length.min',
    ): array {
        $value = str_repeat('x', max(0, $minLength - 1));

        return $this->createTestCase(
            name: $name,
            requestData: TestDataCorruptor::setValue(
                data: $this->validData,
                path: $fieldPath,
                value: $value,
            ),
            expectedValidationErrors: [$errorCode],
        );
    }

    /**
     * Tests URL validation by setting an invalid URL.
     *
     * @param string $name      Test case name
     * @param string $fieldPath Dot-notation path to the field
     * @param string $errorCode Expected validation error code (default: 'validation.url')
     */
    public function invalidUrl(
        string $name,
        string $fieldPath,
        string $errorCode = 'validation.url',
    ): array {
        return $this->createTestCase(
            name: $name,
            requestData: TestDataCorruptor::setValue(
                data: $this->validData,
                path: $fieldPath,
                value: 'invalid-url',
            ),
            expectedValidationErrors: [$errorCode],
        );
    }

    /**
     * Tests email validation by setting an invalid email.
     *
     * @param string $name      Test case name
     * @param string $fieldPath Dot-notation path to the field
     * @param string $errorCode Expected validation error code (default: 'validation.email')
     */
    public function invalidEmail(
        string $name,
        string $fieldPath,
        string $errorCode = 'validation.email',
    ): array {
        return $this->createTestCase(
            name: $name,
            requestData: TestDataCorruptor::setValue(
                data: $this->validData,
                path: $fieldPath,
                value: 'invalid-email',
            ),
            expectedValidationErrors: [$errorCode],
        );
    }

    /**
     * Tests UUID validation by setting an invalid UUID.
     *
     * @param string $name      Test case name
     * @param string $fieldPath Dot-notation path to the field
     * @param string $errorCode Expected validation error code (default: 'validation.uuid')
     */
    public function invalidUuid(
        string $name,
        string $fieldPath,
        string $errorCode = 'validation.uuid',
    ): array {
        return $this->createTestCase(
            name: $name,
            requestData: TestDataCorruptor::setValue(
                data: $this->validData,
                path: $fieldPath,
                value: 'not-a-uuid',
            ),
            expectedValidationErrors: [$errorCode],
        );
    }

    /**
     * Tests generic invalid value with custom validation errors.
     *
     * @param string       $name           Test case name
     * @param string       $fieldPath      Dot-notation path to the field
     * @param mixed        $invalidValue   Invalid value to set
     * @param array|string $expectedErrors Expected validation error code(s)
     */
    public function invalid(
        string $name,
        string $fieldPath,
        mixed $invalidValue,
        array|string $expectedErrors,
    ): array {
        $errors = is_array($expectedErrors) ? $expectedErrors : [$expectedErrors];

        return $this->createTestCase(
            name: $name,
            requestData: TestDataCorruptor::setValue(
                data: $this->validData,
                path: $fieldPath,
                value: $invalidValue,
            ),
            expectedValidationErrors: $errors,
        );
    }
}
