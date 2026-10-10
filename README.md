[![Continuous Integration](https://github.com/hjerichen/framework.database/workflows/Continuous%20Integration/badge.svg?branch=master)](https://github.com/hjerichen/framework.database/actions)
[![Coverage Status](https://coveralls.io/repos/github/hjerichen/framework.database/badge.svg?branch=master&service=github)](https://coveralls.io/github/hjerichen/framework.database?branch=master)

# HJerichen Framework Database

Dieses Paket integriert Doctrine DBAL in das HJerichen Framework. Es stellt eine wiederverwendbare Datenbankverbindung, Schema-Synchronisierung und einfache Persistenz- und Abfragekommandos für DTOs bereit.

## Installation

Voraussetzungen sind PHP 8.3 oder neuer und PDO. Installiere das Paket mit Composer:

```bash
composer require hjerichen/framework.database
```

## Datenbankverbindung

Die Kommandos erhalten eine Doctrine-DBAL-Verbindung. Für eine direkte Verwendung außerhalb des Framework-Object-Factories kannst du sie zum Beispiel so erzeugen:

```php
<?php declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use HJerichen\FrameworkDatabase\DTO\Save\SaveCommand;

$connection = DriverManager::getConnection([
    'url' => $_ENV['DATABASE_URL'],
]);

$saveCommand = new SaveCommand($connection);
```

`DATABASE_URL` muss eine von Doctrine DBAL unterstützte URL sein, zum Beispiel `mysql://user:password@127.0.0.1:3306/app`.

Bei Verwendung des HJerichen Frameworks liest `HJerichen\FrameworkDatabase\Configuration` die Verbindungs-URL aus dem benutzerdefinierten Konfigurationswert `database-url`. Die `ConnectionProvider` erzeugt daraus die gemeinsame DBAL-Verbindung.

## DTOs speichern und abfragen

DTOs implementieren `HJerichen\FrameworkDatabase\DTO\DTO`. Ihre Properties entsprechen den Spalten der Datenbanktabelle. Standardmäßig wird der Klassenname ohne Namespace verwendet und sein erster Buchstabe kleingeschrieben (`App\Model\User` wird zu `user`). Mit `#[Table]` lässt sich der Tabellenname festlegen:

```php
<?php declare(strict_types=1);

namespace App\Model;

use HJerichen\FrameworkDatabase\DTO\Attributes\Table;
use HJerichen\FrameworkDatabase\DTO\DTO;

#[Table('users')]
class User implements DTO
{
    public int $id;
    public string $name;
    public string $email;
}
```

Ein neues Objekt speichern und anschließend nach ID laden:

```php
use App\Model\User;
use HJerichen\FrameworkDatabase\DTO\Query\QueryWithIdsCommand;
use HJerichen\FrameworkDatabase\DTO\Save\SaveCommand;

$user = new User();
$user->name = 'Ada Lovelace';
$user->email = 'ada@example.com';

(new SaveCommand($connection))->execute([$user]);
// Bei einem Insert ohne gesetzte ID wird die generierte ID dem DTO zugewiesen.

$loadedUser = (new QueryWithIdsCommand($connection))
    ->executeForOne(User::class, $user->id);
```

`SaveCommand::execute()` akzeptiert mehrere DTOs in einem Aufruf. Objekte werden nach Tabelle und ihren initialisierten Properties gruppiert; nicht initialisierte typisierte Properties werden beim Insert ausgelassen. Ein gesetztes `id`-Feld wird für Updates beziehungsweise Upserts verwendet.

Für weitere Abfragen stehen spezialisierte Kommandos zur Verfügung:

```php
use HJerichen\FrameworkDatabase\DTO\Delete\DeleteByIdsCommand;
use HJerichen\FrameworkDatabase\DTO\Query\QueryWithFieldFilterCommand;
use HJerichen\FrameworkDatabase\DTO\Query\QueryWithSQLCommand;

// Alle passenden DTOs; Filter werden mit AND verknüpft.
$users = (new QueryWithFieldFilterCommand($connection))->execute(
    User::class,
    ['email' => 'ada@example.com'],
);

// Eigene SQL-Abfrage; Parameter werden separat gebunden.
$users = (new QueryWithSQLCommand($connection))->execute(
    User::class,
    'SELECT * FROM `users` WHERE `email` = :email',
    ['email' => 'ada@example.com'],
);

// Nach einer oder mehreren IDs löschen; Rückgabewert ist die Zahl betroffener Zeilen.
$deletedRows = (new DeleteByIdsCommand($connection))->execute(User::class, [$user->id]);
```

`QueryWithIdsCommand` bietet `execute(string $class, array $ids)` für mehrere Datensätze und `executeForOne(string $class, int $id)` für einen einzelnen Datensatz. `QueryGroupedWithSQLCommand` erlaubt zusätzlich, das Gruppierungsfeld mit `groupBy(string $field)` festzulegen. Abfragekommandos erzeugen DTOs anhand der Spaltennamen und befüllen passende Properties.

## Datenbankschema definieren und synchronisieren

Jede gewünschte Tabelle wird als `Database\Schema\Table` implementiert, die ein Doctrine-DBAL-Tabellenschema zurückgibt. Ein `TablesProvider` liefert die Tabellen gesammelt zurück. Achte darauf, dass Spaltennamen und -typen zu den Properties der DTOs passen.

```php
<?php declare(strict_types=1);

namespace App\Database;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Table as TableSchema;
use Doctrine\DBAL\Types\Types;
use HJerichen\FrameworkDatabase\Database\Schema\Table;
use Override;

class UsersTable implements Table
{
    #[Override]
    public function getSchema(): TableSchema
    {
        return TableSchema::editor()
            ->setUnquotedName('users')
            ->addColumn(
                Column::editor()
                    ->setUnquotedName('id')
                    ->setTypeName(Types::INTEGER)
                    ->setUnsigned(true)
                    ->setAutoincrement(true)
                    ->create()
            )
            ->addColumn(
                Column::editor()
                    ->setUnquotedName('name')
                    ->setTypeName(Types::STRING)
                    ->setLength(255)
                    ->setNotNull(true)
                    ->create()
            )
            ->addColumn(
                Column::editor()
                    ->setUnquotedName('email')
                    ->setTypeName(Types::STRING)
                    ->setLength(255)
                    ->setNotNull(true)
                    ->create()
            )
            ->addPrimaryKeyConstraint(
                PrimaryKeyConstraint::editor()
                    ->setUnquotedColumnNames('id')
                    ->create()
            )
            ->create();
    }
}
```

Der Provider gibt alle vom Projekt verwalteten Tabellen zurück:

```php
<?php declare(strict_types=1);

namespace App\Database;

use HJerichen\FrameworkDatabase\Database\Schema\Table;
use HJerichen\FrameworkDatabase\Database\Schema\TablesProvider;
use Override;

class ApplicationTablesProvider implements TablesProvider
{
    /** @return Table[] */
    #[Override]
    public function getSchemaTables(): array
    {
        return [new UsersTable()];
    }
}
```

`SchemaProvider` stellt das gewünschte und das aktuelle Datenbankschema bereit. `SchemaSynchronizer` berechnet daraus die nötigen MySQL-DDL-Anweisungen und kann sie ausführen:

```php
use HJerichen\FrameworkDatabase\Database\Schema\SchemaProvider;
use HJerichen\FrameworkDatabase\Database\Schema\SchemaSynchronizer;

$schemaProvider = new SchemaProvider($connection, new ApplicationTablesProvider());
$schemaSynchronizer = new SchemaSynchronizer($connection, $schemaProvider);

// Erst die auszuführenden SQL-Anweisungen ansehen:
$sqlStatements = $schemaSynchronizer->calculateQueries();

// Nach Prüfung die Schemaänderungen anwenden:
$executedStatements = $schemaSynchronizer->execute();
```

`execute()` wendet die berechneten Änderungen direkt auf der Datenbank an. Prüfe die Änderungen vor dem Ausführen, insbesondere in produktiven Umgebungen.

## Einbindung in das HJerichen Framework

Der `ObjectFactory` stellt die DBAL-Verbindung und den Schema-Tabellen-Provider für die Framework-Instanziierung bereit. Konfiguriere dazu:

| Schlüssel | Wert |
| --- | --- |
| `database-url` | Doctrine-DBAL-URL, z. B. `mysql://user:password@127.0.0.1:3306/app` |
| `database-schema-tables-provider` | Vollqualifizierter Klassenname einer `TablesProvider`-Implementierung |

Der Provider-Schlüssel ist optional. Wenn er fehlt, wird ein leerer Provider verwendet und es werden keine Anwendungstabellen zur Synchronisierung angemeldet.

## Tests und statische Analyse

```bash
composer validate
vendor/bin/phpunit
vendor/bin/psalm
```

Einen einzelnen Test oder eine einzelne Testmethode ausführen:

```bash
vendor/bin/phpunit tests/Unit/DTO/Save/SaveCommandTest.php
vendor/bin/phpunit --filter testWithOneObject tests/Unit/DTO/Save/SaveCommandTest.php
```

Integrationstests benötigen MySQL 8 und die Datenbank `dbunit`; die Testkonfiguration liest die Verbindung aus `MYSQL_URL`. Unit-Tests verwenden in der Regel gemockte DBAL-Verbindungen und benötigen keinen laufenden Datenbankdienst.
