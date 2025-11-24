<?php

declare(strict_types=1);

namespace SilpoTech\Lib\TestUtilities\Validation;

/**
 * Modifies data structures for validation testing by corrupting valid data.
 *
 * Example transformations:
 * ['title' => 'Valid'] -> setValue('title', '') -> ['title' => '']
 * ['user' => ['name' => 'John']] -> removeValue('user.name') -> ['user' => []]
 */
class TestDataCorruptor
{
    /**
     * Sets a value at the specified path using dot notation.
     *
     * Example: setValue(['title' => 'Valid'], 'title', '') returns ['title' => '']
     * Example: setValue(['user' => ['age' => 25]], 'user.age', -5) returns ['user' => ['age' => -5]]
     */
    public static function setValue(array $data, string $path, mixed $value): array
    {
        $result = $data;
        self::setNestedValue($result, $path, $value);

        return $result;
    }

    /**
     * Removes a value at the specified path using dot notation.
     *
     * Example: removeValue(['title' => 'Valid'], 'title') returns []
     * Example: removeValue(['user' => ['name' => 'John']], 'user.name') returns ['user' => []]
     */
    public static function removeValue(array $data, string $path): array
    {
        $result = $data;
        self::removeNestedValue($result, $path);

        return $result;
    }

    /**
     * Shortcut for setValue($data, $path, []).
     */
    public static function setEmptyArray(array $data, string $path): array
    {
        return self::setValue($data, $path, []);
    }

    /**
     * Navigates through nested arrays using dot notation and sets the value.
     * Creates missing intermediate arrays.
     */
    private static function setNestedValue(array &$data, string $path, mixed $value): void
    {
        $parts = explode('.', $path);
        $current = &$data;

        foreach ($parts as $i => $part) {
            if ($i === count($parts) - 1) {
                $current[$part] = $value;
            } else {
                if (!isset($current[$part])) {
                    $current[$part] = [];
                }
                $current = &$current[$part];
            }
        }
    }

    /**
     * Navigates to the target field and removes it.
     * Stops early if path doesn't exist.
     */
    private static function removeNestedValue(array &$data, string $path): void
    {
        $parts = explode('.', $path);
        $current = &$data;

        for ($i = 0; $i < count($parts) - 1; ++$i) {
            $part = $parts[$i];
            if (!isset($current[$part])) {
                return;
            }
            $current = &$current[$part];
        }

        unset($current[$parts[count($parts) - 1]]);
    }
}
