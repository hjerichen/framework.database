<?php declare(strict_types=1);

namespace HJerichen\FrameworkDatabase\DTO\Save;

use DateTimeInterface;
use Doctrine\DBAL\Connection;
use HJerichen\Collections\Collection;
use HJerichen\FrameworkDatabase\DTO\DTO;
use HJerichen\FrameworkDatabase\DTO\QuoteTableColumnTrait;
use HJerichen\FrameworkDatabase\DTO\TableNameResolver\TableNameResolver;
use HJerichen\FrameworkDatabase\DTO\TableNameResolver\TableNameResolverAttribute;
use HJerichen\FrameworkDatabase\DTO\TableNameResolver\TableNameResolverBase;
use HJerichen\FrameworkDatabase\DTO\Utils;
use ReflectionClass;

class SaveCommand
{
    use QuoteTableColumnTrait;

    private TableNameResolver $tableNameResolver;

    /** @var string[][] */
    private array $allFieldsForTable;
    /** @var array<string,array<string,list<DTO>>> */
    private array $groupedObjects;

    public function __construct(
        private readonly Connection $connection
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
            foreach (array_keys($groupedObjects) as $fieldsString) {
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

    /** @param string[] $fields */
    private function buildSQL(string $tableName, array $fields, int $objectCount): string
    {
        $fieldsSQL = $this->buildFieldsSQL($fields);
        $valuesSQL = $this->buildValuesSQL($fields, $objectCount);
        $duplicateSQL = $this->buildDuplicateSQL($tableName, $fields);
        return <<<SQL
            INSERT INTO {$this->quoteTableName($tableName)}
                $fieldsSQL
            VALUES
                $valuesSQL
            ON DUPLICATE KEY UPDATE
                $duplicateSQL
            SQL;
    }

    /** @param string[] $fields */
    private function buildFieldsSQL(array $fields): string
    {
        $fields = array_map([$this, 'quoteColumnName'], $fields);
        return '(' . implode(', ', $fields) . ')';
    }

    /** @param string[] $fields */
    private function buildValuesSQL(array $fields, int $objectCount): string
    {
        $valuesSQLs = [];
        for ($i = 1; $i <= $objectCount; $i++) {
            $fieldsForIndex = array_map(static fn (string $field) => ":{$field}_$i", $fields);
            $valuesSQLs[] = '(' . implode(', ', $fieldsForIndex) . ')';
        }
        return implode(', ', $valuesSQLs);
    }

    /** @param string[] $fields */
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

    /**
     * @param string[] $fields
     * @param list<DTO> $objects
     * @return array<string,int|float|string|null>
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection UnknownInspectionInspection
     */
    private function buildParameters(array $fields, array $objects): array
    {
        $parameters = [];
        foreach ($objects as $index => $object) {
            foreach ($fields as $field) {
                $key = $field . '_' . ($index + 1);
                /** @var mixed $value */
                $value = $object->$field;
                $parameters[$key] = $this->convertValueForParameter($value);
            }
        }
        return $parameters;
    }

    private function convertValueForParameter(mixed $value): int|float|string|null
    {
        if ($value instanceof Collection) {
            $mapping = fn(mixed $value): int|float|string|null => $this->convertValueForParameter($value);
            $values = $value->map($mapping);
            return json_encode($values, JSON_THROW_ON_ERROR);
        }
        if ($value instanceof DTO) {
            return json_encode(Utils::convertToHash($value), JSON_THROW_ON_ERROR);
        }
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        if (is_array($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR);
        }
        if (is_bool($value)) {
            return (int)$value;
        }
        if (is_numeric($value) || is_null($value)) {
            return $value;
        }
        return (string)$value;
    }

    /**
     * @param list<DTO> $objects
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection UnknownInspectionInspection
     */
    private function assignIdsToObjects(array $objects): void
    {
        $id = (int)$this->connection->lastInsertId();
        /** @psalm-suppress NoInterfaceProperties */
        foreach ($objects as $object) {
            $object->id = $id++;
        }
    }
}
