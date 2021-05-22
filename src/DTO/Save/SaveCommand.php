<?php
/**
 * @noinspection SyntaxError
 * @noinspection UnknownInspectionInspection
 */
declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO\Save;

use Doctrine\DBAL\Connection;
use HJerichen\FrameworkDatabase\DTO\DTO;
use HJerichen\FrameworkDatabase\DTO\QuoteTableColumnTrait;
use ReflectionClass;

class SaveCommand
{
    use QuoteTableColumnTrait;

    /** @var DTO[][] */
    private array $groupedObjects;

    public function __construct(
        private Connection $connection
    ) {
    }

    /**
     * @param DTO[] $objects
     */
    public function execute(array $objects): void
    {
        $this->setObjects($objects);
        $this->saveObjects();
    }

    /**
     * @param DTO[] $objects
     */
    private function setObjects(array $objects): void
    {
        $this->groupedObjects = [];
        foreach ($objects as $object) {
            $tableName = $this->getTableName($object);
            $fieldsString = $this->buildFieldsString($object);
            $this->groupedObjects[$tableName][$fieldsString][] = $object;
        }
    }

    protected function getTableName(DTO $object): string
    {
        $exploded = explode('\\', $object::class);
        return lcfirst(end($exploded));
    }

    private function buildFieldsString(object $object): string
    {
        $fields = [];
        $reflection = new ReflectionClass($object);
        foreach ($reflection->getProperties() as $property) {
            if ($property->isInitialized($object)) {
                $fields[] = $property->name;
            }
        }
        return implode('|', $fields);
    }

    private function saveObjects(): void
    {
        foreach ($this->groupedObjects as $tableName => $groupedObjects) {
            foreach ($groupedObjects as $fieldsString => $objects) {
                $this->saveObjectsForTable($tableName, $fieldsString);
            }
        }
    }

    private function saveObjectsForTable(string $tableName, string $fieldsString): void
    {
        $objects = $this->groupedObjects[$tableName][$fieldsString] ?? [];
        $fields = explode('|', $fieldsString);
        if ($fields === ['id']) return;

        $sql = $this->buildSQL($tableName, $fields, count($objects));
        $parameters = $this->buildParameters($fields, $objects);
        $this->connection->executeStatement($sql, $parameters);

        if (!in_array('id', $fields)) {
            $this->assignIdsToObjects($objects);
        }
    }

    private function buildSQL(string $tableName, array $fields, int $objectCount): string
    {
        $fieldsSQL = $this->buildFieldsSQL($fields);
        $valuesSQL = $this->buildValuesSQL($fields, $objectCount);
        $duplicateSQL = $this->buildDuplicateSQL($fields);
        return "
            INSERT INTO {$this->quoteTableName($tableName)}
                $fieldsSQL
            VALUES
                $valuesSQL
            ON DUPLICATE KEY UPDATE
                $duplicateSQL
        ";
    }

    private function buildFieldsSQL(array $fields): string
    {
        $fields = array_map([$this, 'quoteColumnName'], $fields);
        return '(' . implode(', ', $fields) . ')';
    }

    private function buildValuesSQL(array $fields, int $objectCount): string
    {
        $valuesSQLs = [];
        for ($i = 1; $i <= $objectCount; $i++) {
            $fieldsForIndex = array_map(static fn (string $field) => ":{$field}_$i", $fields);
            $valuesSQLs[] = '(' . implode(', ', $fieldsForIndex) . ')';
        }
        return implode(', ', $valuesSQLs);
    }

    private function buildDuplicateSQL(array $fields): string
    {
        $fieldStrings = [];
        foreach ($fields as $field) {
            if ($field === 'id') continue;
            $field = $this->quoteColumnName($field);
            $fieldStrings[] = "$field = VALUES($field)";
        }
        return implode(', ', $fieldStrings);
    }

    private function buildParameters(array $fields, array $objects): array
    {
        $parameters = [];
        foreach ($objects as $index => $object) {
            foreach ($fields as $field) {
                $key = $field . '_' . ($index + 1);
                $parameters[$key] = $object->$field;
            }
        }
        return $parameters;
    }

    private function assignIdsToObjects(array $objects): void
    {
        $id = (int)$this->connection->lastInsertId();
        foreach ($objects as $object) {
            $object->id = $id++;
        }
    }
}
