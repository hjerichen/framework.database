<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO;

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
        if (is_subclass_of($type->getName(), Enum::class)) {
            return call_user_func([$type->getName(), 'from'], $value);
        }
        return match ($type->getName()) {
            'int' => $type->allowsNull() && $value === null ? null : (int)$value,
            'bool' => $type->allowsNull() && $value === null ? null : (bool)$value,
            'float' => $type->allowsNull() && $value === null ? null : (float)$value,
            'string' => $type->allowsNull() && $value === null ? null : (string)$value,
            default => $value,
        };
    }
}
