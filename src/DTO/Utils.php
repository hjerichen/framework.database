<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO;

use DateTime;
use DateTimeImmutable;
use HJerichen\Collections\Collection;
use HJerichen\Collections\ObjectCollection;
use HJerichen\Framework\Types\Enum;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionType;
use RuntimeException;

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

    /**
     * @template T extends ObjectCollection
     * @param class-string<T> $collectionClass
     * @param array[] $hashes
     * @return T
     * @psalm-suppress MismatchingDocblockReturnType, MixedMethodCall
     */
    public static function buildDTOCollection(string $collectionClass, array $hashes): ObjectCollection
    {
        $collection = new $collectionClass;
        if (!($collection instanceof ObjectCollection)) {
            throw new RuntimeException('No collection provided.');
        }

        $objects = self::buildDTOs($collection->getType(), $hashes);
        $collection->pushMultiple($objects);
        return $collection;
    }

    /**
     * @template T extends DTO
     * @param class-string<T> $class
     * @param array[] $hashes
     * @return T[]
     */
    public static function buildDTOs(string $class, array $hashes): array
    {
        return array_map(static fn(array $hash) => self::buildDTO($class, $hash), $hashes);
    }

    /**
     * @template T extends DTO
     * @param class-string<T> $class
     * @param array $hash
     * @return T
     * @psalm-suppress MismatchingDocblockReturnType, MixedMethodCall
     */
    public static function buildDTO(string $class, array $hash): DTO
    {
        $dto = new $class;
        if (!($dto instanceof DTO)) {
            throw new RuntimeException("Class $class does not implement DTO.");
        }
        self::populateObject($dto, $hash);
        return $dto;
    }

    /** @psalm-suppress MixedAssignment, ArgumentTypeCoercion */
    public static function convertToHash(DTO $object): array
    {
        $hash = self::convertObjectToArray($object);
        foreach ($hash as $key => $value) {
            if ($value instanceof DTO) {
                $hash[$key] = self::convertToHash($value);
            } elseif ($value instanceof ObjectCollection) {
                $hash[$key] = self::convertToHashes($value);
            }
        }
        return $hash;
    }

    /** @param ObjectCollection<DTO> $objects */
    public static function convertToHashes(ObjectCollection $objects): array {
        return $objects->map(self::convertToHash(...));
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
        if ($typeName === 'array') {
            return is_string($value)
                ? json_decode($value, true, 512, JSON_THROW_ON_ERROR)
                : $value;
        }
        return match ($typeName) {
            'int' => (int)$value,
            'bool' => (bool)$value,
            'float' => (float)$value,
            'string' => (string)$value,
            default => $value,
        };
    }
}
