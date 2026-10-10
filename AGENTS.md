# Agent instructions

## Build, test, and analysis

This is a Composer-managed PHP library (PHP `^8.3`). There is no separate build or lint command configured.

- Validate Composer metadata: `composer validate`
- Run the test suite: `vendor/bin/phpunit`
- Run one test file: `vendor/bin/phpunit tests/Unit/DTO/Save/SaveCommandTest.php`
- Run one test method: `vendor/bin/phpunit --filter testWithOneObject tests/Unit/DTO/Save/SaveCommandTest.php`
- Run static analysis: `vendor/bin/psalm`

Integration tests use MySQL through `MYSQL_URL`, configured in `phpunit.xml`; CI starts MySQL 8 and creates the `dbunit` database. Unit tests generally mock Doctrine DBAL connections and do not need the database service.

## Architecture

The library wraps Doctrine DBAL behind the framework's configuration and object-factory mechanisms. `Configuration` reads `database-url` and the optional `database-schema-tables-provider` setting. `ObjectFactory` creates the configured `ConnectionProvider` and schema table provider; `ConnectionProvider` parses the URL and supplies the shared DBAL connection.

Schema definitions are provided as `Database\Schema\Table` implementations collected by a `TablesProvider`. `SchemaProvider` compares those desired table schemas with the live database schema, and `SchemaSynchronizer` calculates and executes the resulting MySQL schema changes.

The DTO persistence layer treats DTO properties as database fields. Query commands share SQL execution, table-name resolution, and row-to-object hydration through `QueryCommandAbstract` and `Utils`; save and delete behavior lives in separate commands. The default table name is the DTO class's short name with its first character lowercased, and `#[DTO\Attributes\Table]` can override it. `SaveCommand` batches objects by table and initialized-property set, so unset typed properties are omitted from inserts and updates. DTOs and their schema definitions therefore need to agree on field names and types.

## Repository conventions

- Production classes use the `HJerichen\FrameworkDatabase\` PSR-4 namespace rooted at `src/`; tests use `HJerichen\FrameworkDatabase\Test\` rooted at `tests/`.
- PHP files generally enable `strict_types=1`, use constructor property promotion and typed properties, and mark interface implementations with `#[Override]`.
- Add or change database columns in the relevant `Table::getSchema()` definition as well as in DTOs or persistence logic. Schema tests should exercise the desired/current schema comparison; persistence tests should cover generated SQL and parameter binding.
- Keep unit tests under `tests/Unit` with mocked DBAL dependencies. Tests requiring real MySQL belong under `tests/Integration` and should use `Testing\DatabaseTestCase`, overriding `getSchemaTablesProvider()` when their schema needs tables.
- Database values are bound as parameters; identifiers are quoted through `QuoteTableColumnTrait`. Preserve that separation when changing query construction.
