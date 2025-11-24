<?php

declare(strict_types=1);

namespace Tests\TestCase\Validation;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SilpoTech\Lib\TestUtilities\Validation\ValidationTestHelper;

class ValidationTestHelperTest extends TestCase
{
    private array $validData;
    private ValidationTestHelper $helper;

    protected function setUp(): void
    {
        $this->validData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 25,
            'role' => 'user',
            'website' => 'https://example.com',
            'user' => [
                'profile' => [
                    'bio' => 'Software developer',
                ],
            ],
        ];

        $this->helper = new ValidationTestHelper($this->validData);
    }

    #[Test]
    public function enumCreatesTestCaseWithInvalidEnumValue(): void
    {
        $result = $this->helper->enum('Invalid Role', 'role', 'invalid_role');

        $this->assertSame('Invalid Role', $result['name']);
        $this->assertSame(400, $result['expectedStatusCode']);
        $this->assertSame('invalid_role', $result['requestData']['role']);
        $this->assertSame(['validation.choice'], $result['expectedValidationErrors']);
    }

    #[Test]
    public function enumWithCustomErrorCodeUsesCustomErrorCode(): void
    {
        $result = $this->helper->enum('Invalid Role', 'role', 'admin', 'custom.error');

        $this->assertSame(['custom.error'], $result['expectedValidationErrors']);
    }

    #[Test]
    public function requiredRemovesFieldFromData(): void
    {
        $result = $this->helper->required('Missing Name', 'name');

        $this->assertSame('Missing Name', $result['name']);
        $this->assertSame(400, $result['expectedStatusCode']);
        $this->assertArrayNotHasKey('name', $result['requestData']);
        $this->assertSame(['validation.not_null'], $result['expectedValidationErrors']);
    }

    #[Test]
    public function requiredWithNestedFieldRemovesNestedField(): void
    {
        $result = $this->helper->required('Missing Bio', 'user.profile.bio');

        $this->assertArrayHasKey('user', $result['requestData']);
        $this->assertArrayHasKey('profile', $result['requestData']['user']);
        $this->assertArrayNotHasKey('bio', $result['requestData']['user']['profile']);
    }

    #[Test]
    public function requiredWithCustomErrorCodeUsesCustomErrorCode(): void
    {
        $result = $this->helper->required('Missing Name', 'name', 'validation.not_blank');

        $this->assertSame(['validation.not_blank'], $result['expectedValidationErrors']);
    }

    #[Test]
    public function tooLongCreatesStringExceedingMaxLength(): void
    {
        $result = $this->helper->tooLong('Name Too Long', 'name', 50);

        $this->assertSame('Name Too Long', $result['name']);
        $this->assertSame(60, strlen($result['requestData']['name']));
        $this->assertSame(['validation.length.max'], $result['expectedValidationErrors']);
    }

    #[Test]
    public function tooLongWithCustomErrorCodeUsesCustomErrorCode(): void
    {
        $result = $this->helper->tooLong('Name Too Long', 'name', 50, 'custom.max');

        $this->assertSame(['custom.max'], $result['expectedValidationErrors']);
    }

    #[Test]
    public function tooShortCreatesStringShorterThanMinLength(): void
    {
        $result = $this->helper->tooShort('Name Too Short', 'name', 5);

        $this->assertSame('Name Too Short', $result['name']);
        $this->assertSame(4, strlen($result['requestData']['name']));
        $this->assertSame(['validation.length.min'], $result['expectedValidationErrors']);
    }

    #[Test]
    public function tooShortWithZeroMinLengthCreatesEmptyString(): void
    {
        $result = $this->helper->tooShort('Empty Name', 'name', 1);

        $this->assertSame('', $result['requestData']['name']);
    }

    #[Test]
    public function tooShortWithCustomErrorCodeUsesCustomErrorCode(): void
    {
        $result = $this->helper->tooShort('Name Too Short', 'name', 5, 'custom.min');

        $this->assertSame(['custom.min'], $result['expectedValidationErrors']);
    }

    #[Test]
    public function invalidUrlSetsInvalidUrlValue(): void
    {
        $result = $this->helper->invalidUrl('Invalid Website', 'website');

        $this->assertSame('Invalid Website', $result['name']);
        $this->assertSame('invalid-url', $result['requestData']['website']);
        $this->assertSame(['validation.url'], $result['expectedValidationErrors']);
    }

    #[Test]
    public function invalidUrlWithCustomErrorCodeUsesCustomErrorCode(): void
    {
        $result = $this->helper->invalidUrl('Invalid Website', 'website', 'custom.url');

        $this->assertSame(['custom.url'], $result['expectedValidationErrors']);
    }

    #[Test]
    public function invalidEmailSetsInvalidEmailValue(): void
    {
        $result = $this->helper->invalidEmail('Invalid Email', 'email');

        $this->assertSame('Invalid Email', $result['name']);
        $this->assertSame('invalid-email', $result['requestData']['email']);
        $this->assertSame(['validation.email'], $result['expectedValidationErrors']);
    }

    #[Test]
    public function invalidEmailWithCustomErrorCodeUsesCustomErrorCode(): void
    {
        $result = $this->helper->invalidEmail('Invalid Email', 'email', 'custom.email');

        $this->assertSame(['custom.email'], $result['expectedValidationErrors']);
    }

    #[Test]
    public function invalidUuidSetsInvalidUuidValue(): void
    {
        $validData = ['id' => '123e4567-e89b-12d3-a456-426614174000'];
        $helper = new ValidationTestHelper($validData);

        $result = $helper->invalidUuid('Invalid UUID', 'id');

        $this->assertSame('Invalid UUID', $result['name']);
        $this->assertSame('not-a-uuid', $result['requestData']['id']);
        $this->assertSame(['validation.uuid'], $result['expectedValidationErrors']);
    }

    #[Test]
    public function invalidUuidWithCustomErrorCodeUsesCustomErrorCode(): void
    {
        $validData = ['id' => '123e4567-e89b-12d3-a456-426614174000'];
        $helper = new ValidationTestHelper($validData);

        $result = $helper->invalidUuid('Invalid UUID', 'id', 'custom.uuid');

        $this->assertSame(['custom.uuid'], $result['expectedValidationErrors']);
    }

    #[Test]
    public function invalidWithSingleErrorCreatesSingleErrorArray(): void
    {
        $result = $this->helper->invalid('Negative Age', 'age', -5, 'validation.positive');

        $this->assertSame('Negative Age', $result['name']);
        $this->assertSame(-5, $result['requestData']['age']);
        $this->assertSame(['validation.positive'], $result['expectedValidationErrors']);
    }

    #[Test]
    public function invalidWithMultipleErrorsCreatesMultipleErrorArray(): void
    {
        $result = $this->helper->invalid(
            'Empty Name',
            'name',
            '',
            ['validation.not_blank', 'validation.length.min'],
        );

        $this->assertSame('Empty Name', $result['name']);
        $this->assertSame('', $result['requestData']['name']);
        $this->assertSame(
            ['validation.not_blank', 'validation.length.min'],
            $result['expectedValidationErrors'],
        );
    }

    #[Test]
    public function invalidWithNullValueSetsNullValue(): void
    {
        $result = $this->helper->invalid('Null Age', 'age', null, 'validation.not_null');

        $this->assertNull($result['requestData']['age']);
    }

    #[Test]
    public function invalidWithArrayValueSetsArrayValue(): void
    {
        $result = $this->helper->invalid('Invalid Type', 'age', ['not', 'a', 'number'], 'validation.type');

        $this->assertSame(['not', 'a', 'number'], $result['requestData']['age']);
    }

    #[Test]
    public function constructorWithCustomStatusCodeUsesCustomStatusCode(): void
    {
        $helper = new ValidationTestHelper($this->validData, expectedStatusCode: 422);

        $result = $helper->required('Missing Name', 'name');

        $this->assertSame(422, $result['expectedStatusCode']);
    }

    #[Test]
    public function allMethodsDoNotModifyOriginalValidData(): void
    {
        $originalData = $this->validData;

        $this->helper->required('Test', 'name');
        $this->helper->enum('Test', 'role', 'invalid');
        $this->helper->tooLong('Test', 'name', 5);
        $this->helper->invalid('Test', 'age', -1, 'error');

        $this->assertSame($originalData, $this->validData);
    }

    #[Test]
    public function testCaseHasExpectedStructure(): void
    {
        $result = $this->helper->required('Test', 'name');

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('expectedStatusCode', $result);
        $this->assertArrayHasKey('requestData', $result);
        $this->assertArrayHasKey('expectedValidationErrors', $result);
        $this->assertIsString($result['name']);
        $this->assertIsInt($result['expectedStatusCode']);
        $this->assertIsArray($result['requestData']);
        $this->assertIsArray($result['expectedValidationErrors']);
    }
}
