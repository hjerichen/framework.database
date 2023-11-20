<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO;

use DateTime;
use DateTimeImmutable;
use HJerichen\Collections\Collection;
use HJerichen\Framework\Types\Enum;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionType;

class Utils
{
    /** @psalm-suppress MixedAssignment */
    public static function convertObjectToArray(DTO $object): array
    {
        $class = new ReflectionClass($object);
        $array = [];

        foreach ($class->getProperties() as $property) {
            if ($property->isInitialized($object)) {
                $array[$property->name] = $property->getValue($object);
            }
        }
        return $array;
    }

    /** @psalm-suppress MixedAssignment */
    public static function populateObject(DTO $object, array $data): void
    {
        $class = new ReflectionClass($object);

        foreach ($class->getProperties() as $property) {
            $type = $property->getType();
            $name = $property->name;
            if (array_key_exists($name, $data)) {
                $value = self::convertToCorrectType($type, $data[$name]);
                $object->$name = $value;
            }
        }
    }

    private static function convertToCorrectType(ReflectionType|null $type, mixed $value): mixed
    {
        if (!($type instanceof ReflectionNamedType)) return $value;
        if ($value === null && $type->allowsNull())  return null;

        return self::convertToCorrectTypeWithName($type->getName(), $value);
    }

    /**
     * @psalm-suppress UnsafeInstantiation
     * @psalm-suppress MixedAssignment
     * @psalm-suppress MixedArgument
     */
    private static function convertToCorrectTypeWithName(string $typeName, mixed $value): mixed
    {
        if (is_subclass_of($typeName, DTO::class)) {
            $object = new $typeName;
            $data = is_string($value)
                ? json_decode($value, true, 512, JSON_THROW_ON_ERROR)
                : $value;
            self::populateObject($object, $data);
            return $object;
        }
        if (is_subclass_of($typeName, Enum::class)) {
            return call_user_func([$typeName, 'from'], $value);
        }
        if (is_subclass_of($typeName, Collection::class)) {
            $items = is_string($value)
                ? json_decode($value, true, 512, JSON_THROW_ON_ERROR)
                : $value;
            $collection = new $typeName();
            foreach ($items as $item) {
                $collection[] = self::convertToCorrectTypeWithName($collection->getType(), $item);
            }
            return $collection;
        }
        if ($typeName === DateTime::class || is_subclass_of($typeName, DateTime::class)) {
            return new DateTime($value);
        }
        if ($typeName === DateTimeImmutable::class || is_subclass_of($typeName, DateTimeImmutable::class)) {
            return new DateTimeImmutable($value);
        }
        return match ($typeName) {
            'int' => (int)$value,
            'bool' => (bool)$value,
            'float' => (float)$value,
            'string' => (string)$value,
            'array' => json_decode($value, true, 512, JSON_THROW_ON_ERROR),
            default => $value,
        };
    }
}
