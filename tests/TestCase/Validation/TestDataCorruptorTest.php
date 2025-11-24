<?php

declare(strict_types=1);

namespace Tests\TestCase\Validation;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SilpoTech\Lib\TestUtilities\Validation\TestDataCorruptor;

class TestDataCorruptorTest extends TestCase
{
    #[Test]
    public function setValue_withSimpleField_setsValue(): void
    {
        $data = ['title' => 'Valid Title'];

        $result = TestDataCorruptor::setValue($data, 'title', 'Modified');

        $this->assertSame(['title' => 'Modified'], $result);
    }

    #[Test]
    public function setValue_withNestedField_setsNestedValue(): void
    {
        $data = ['user' => ['name' => 'John', 'age' => 25]];

        $result = TestDataCorruptor::setValue($data, 'user.age', 30);

        $expected = ['user' => ['name' => 'John', 'age' => 30]];
        $this->assertSame($expected, $result);
    }

    #[Test]
    public function setValue_withDeeplyNestedField_setsValue(): void
    {
        $data = ['level1' => ['level2' => ['level3' => 'value']]];

        $result = TestDataCorruptor::setValue($data, 'level1.level2.level3', 'modified');

        $expected = ['level1' => ['level2' => ['level3' => 'modified']]];
        $this->assertSame($expected, $result);
    }

    #[Test]
    public function setValue_withNonExistentPath_createsIntermediateArrays(): void
    {
        $data = ['existing' => 'value'];

        $result = TestDataCorruptor::setValue($data, 'new.nested.field', 'test');

        $expected = [
            'existing' => 'value',
            'new' => ['nested' => ['field' => 'test']],
        ];
        $this->assertSame($expected, $result);
    }

    #[Test]
    public function setValue_withArrayIndex_setsValueAtIndex(): void
    {
        $data = ['items' => ['first', 'second', 'third']];

        $result = TestDataCorruptor::setValue($data, 'items.1', 'modified');

        $expected = ['items' => ['first', 'modified', 'third']];
        $this->assertSame($expected, $result);
    }

    #[Test]
    public function setValue_withEmptyString_setsEmptyString(): void
    {
        $data = ['field' => 'value'];

        $result = TestDataCorruptor::setValue($data, 'field', '');

        $this->assertSame(['field' => ''], $result);
    }

    #[Test]
    public function setValue_withNull_setsNull(): void
    {
        $data = ['field' => 'value'];

        $result = TestDataCorruptor::setValue($data, 'field', null);

        $this->assertSame(['field' => null], $result);
    }

    #[Test]
    public function setValue_doesNotModifyOriginalArray(): void
    {
        $data = ['field' => 'original'];

        TestDataCorruptor::setValue($data, 'field', 'modified');

        $this->assertSame(['field' => 'original'], $data);
    }

    #[Test]
    public function removeValue_withSimpleField_removesField(): void
    {
        $data = ['keep' => 'value', 'remove' => 'this'];

        $result = TestDataCorruptor::removeValue($data, 'remove');

        $this->assertSame(['keep' => 'value'], $result);
    }

    #[Test]
    public function removeValue_withNestedField_removesNestedField(): void
    {
        $data = ['user' => ['name' => 'John', 'age' => 25]];

        $result = TestDataCorruptor::removeValue($data, 'user.age');

        $expected = ['user' => ['name' => 'John']];
        $this->assertSame($expected, $result);
    }

    #[Test]
    public function removeValue_withDeeplyNestedField_removesField(): void
    {
        $data = [
            'level1' => [
                'level2' => [
                    'keep' => 'this',
                    'remove' => 'this',
                ],
            ],
        ];

        $result = TestDataCorruptor::removeValue($data, 'level1.level2.remove');

        $expected = ['level1' => ['level2' => ['keep' => 'this']]];
        $this->assertSame($expected, $result);
    }

    #[Test]
    public function removeValue_withNonExistentPath_doesNothing(): void
    {
        $data = ['existing' => 'value'];

        $result = TestDataCorruptor::removeValue($data, 'nonexistent.path');

        $this->assertSame($data, $result);
    }

    #[Test]
    public function removeValue_withArrayIndex_removesIndexedValue(): void
    {
        $data = ['items' => ['first', 'second', 'third']];

        $result = TestDataCorruptor::removeValue($data, 'items.1');

        $expected = ['items' => ['first', 2 => 'third']];
        $this->assertSame($expected, $result);
    }

    #[Test]
    public function removeValue_doesNotModifyOriginalArray(): void
    {
        $data = ['field' => 'value'];

        TestDataCorruptor::removeValue($data, 'field');

        $this->assertSame(['field' => 'value'], $data);
    }

    #[Test]
    public function setEmptyArray_setsEmptyArray(): void
    {
        $data = ['field' => ['has', 'values']];

        $result = TestDataCorruptor::setEmptyArray($data, 'field');

        $this->assertSame(['field' => []], $result);
    }

    #[Test]
    public function setEmptyArray_withNestedPath_setsEmptyArray(): void
    {
        $data = ['user' => ['items' => ['a', 'b', 'c']]];

        $result = TestDataCorruptor::setEmptyArray($data, 'user.items');

        $expected = ['user' => ['items' => []]];
        $this->assertSame($expected, $result);
    }

    #[Test]
    #[DataProvider('complexDataProvider')]
    public function setValue_withComplexStructures_worksCorrectly(
        array $data,
        string $path,
        mixed $value,
        array $expected,
    ): void {
        $result = TestDataCorruptor::setValue($data, $path, $value);

        $this->assertSame($expected, $result);
    }

    public static function complexDataProvider(): iterable
    {
        yield 'nested array with numeric keys' => [
            'data' => ['users' => [['name' => 'John'], ['name' => 'Jane']]],
            'path' => 'users.0.name',
            'value' => 'Modified',
            'expected' => ['users' => [['name' => 'Modified'], ['name' => 'Jane']]],
        ];

        yield 'mixed keys' => [
            'data' => ['data' => ['items' => ['key1' => 'value1', 0 => 'value2']]],
            'path' => 'data.items.0',
            'value' => 'modified',
            'expected' => ['data' => ['items' => ['key1' => 'value1', 0 => 'modified']]],
        ];

        yield 'overwrite entire nested structure' => [
            'data' => ['config' => ['nested' => ['deep' => 'value']]],
            'path' => 'config.nested',
            'value' => 'flat',
            'expected' => ['config' => ['nested' => 'flat']],
        ];
    }
}
