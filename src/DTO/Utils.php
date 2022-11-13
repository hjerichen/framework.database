<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO;

use DateTime;
use DateTimeImmutable;
use HJerichen\Framework\Types\Enum;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionType;

class Utils
{
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

        if (is_subclass_of($type->getName(), Enum::class)) {
            return call_user_func([$type->getName(), 'from'], $value);
        }
        if ($type->getName() === DateTime::class || is_subclass_of($type->getName(), DateTime::class)) {
            return new DateTime($value);
        }
        if ($type->getName() === DateTimeImmutable::class || is_subclass_of($type->getName(), DateTimeImmutable::class)) {
            return new DateTimeImmutable($value);
        }
        return match ($type->getName()) {
            'int' => (int)$value,
            'bool' => (bool)$value,
            'float' => (float)$value,
            'string' => (string)$value,
            'array' => json_decode($value, true, 512, JSON_THROW_ON_ERROR),
            default => $value,
        };
    }
}
