<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO;

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
        if (!$type->isBuiltin()) return $value;

        return match ($type->getName()) {
            'int' => (int)$value,
            'bool' => (bool)$value,
            'float' => (float)$value,
            'string' => (string)$value,
            default => $value,
        };
    }
}
