<?php
/**
 * @noinspection SyntaxError
 * @noinspection UnknownInspectionInspection
 */
declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO\Save;

use DateTimeInterface;
use Doctrine\DBAL\Connection;
use HJerichen\FrameworkDatabase\DTO\DTO;
use HJerichen\FrameworkDatabase\DTO\QuoteTableColumnTrait;
use HJerichen\FrameworkDatabase\DTO\TableNameResolver\TableNameResolver;
use HJerichen\FrameworkDatabase\DTO\TableNameResolver\TableNameResolverAttribute;
use HJerichen\FrameworkDatabase\DTO\TableNameResolver\TableNameResolverBase;
use ReflectionClass;

class SaveCommand
{
    use QuoteTableColumnTrait;

    private TableNameResolver $tableNameResolver;

    /** @var string[][] */
    private array $allFieldsForTable;
    /** @var DTO[][] */
    private array $groupedObjects;

    public function __construct(
        private Connection $connection
    ) {
        $this->tableNameResolver = new TableNameResolverAttribute(new TableNameResolverBase());
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
        $this->allFieldsForTable = [];
        $this->groupedObjects = [];

        foreach ($objects as $object) {
            $this->gatherAllFieldsForTable($object);
            $tableName = $this->getTableName($object);
            $fieldsString = $this->buildFieldsString($object);
            $this->groupedObjects[$tableName][$fieldsString][] = $object;
        }
    }

    protected function getTableName(DTO $object): string
    {
        return $this->tableNameResolver->getTableName($object);
    }

    private function gatherAllFieldsForTable(DTO $object): void
    {
        $tableName = $this->getTableName($object);
        if (isset($this->allFieldsForTable[$tableName])) return;

        $class = new ReflectionClass($object);
        foreach ($class->getProperties() as $property) {
            $this->allFieldsForTable[$tableName][] = $property->name;
        }
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
        $duplicateSQL = $this->buildDuplicateSQL($tableName, $fields);
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

    private function buildDuplicateSQL(string $tableName, array $fields): string
    {
        $fieldStrings = [];
        foreach ($fields as $field) {
            if ($field === 'id') continue;
            $field = $this->quoteColumnName($field);
            $fieldStrings[] = "$field = VALUES($field)";
        }

        $missingFields = array_diff($this->allFieldsForTable[$tableName], $fields);
        foreach ($missingFields as $field) {
            if ($field === 'id') continue;
            $field = $this->quoteColumnName($field);
            $fieldStrings[] = "$field = $field";
        }
        return implode(', ', $fieldStrings);
    }

    private function buildParameters(array $fields, array $objects): array
    {
        $parameters = [];
        foreach ($objects as $index => $object) {
            foreach ($fields as $field) {
                $key = $field . '_' . ($index + 1);
                $value = $object->$field;
                if ($value instanceof DateTimeInterface) {
                    $parameters[$key] = $value->format('Y-m-d H:i:s');
                } elseif (is_array($value)) {
                    $parameters[$key] = json_encode($value, JSON_THROW_ON_ERROR);
                } elseif (is_bool($value)) {
                    $parameters[$key] = (int)$value;
                } else {
                    $parameters[$key] = $value;
                }
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
