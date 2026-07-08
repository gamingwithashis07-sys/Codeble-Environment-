<?php

declare(strict_types=1);

namespace LoveGem\Database\Migrations;

class Blueprint
{
    protected string $table;

    protected bool $create = false;

    protected array $columns = [];

    protected array $indexes = [];

    protected array $uniques = [];

    protected array $foreignKeys = [];

    public function __construct(string $table, bool $create = false)
    {
        $this->table = $table;
        $this->create = $create;
    }

    public function id(string $column = 'id'): void
    {
        $this->increments($column);
    }

    public function increments(string $column): void
    {
        $this->columns[] = "`{$column}` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY";
    }

    public function bigIncrements(string $column): void
    {
        $this->columns[] = "`{$column}` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY";
    }

    public function integer(string $column, bool $unsigned = false, bool $autoIncrement = false): void
    {
        $unsignedStr = $unsigned ? ' UNSIGNED' : '';
        $autoStr = $autoIncrement ? ' AUTO_INCREMENT' : '';
        $this->columns[] = "`{$column}` INT{$unsignedStr}{$autoStr}";
    }

    public function bigInteger(string $column, bool $unsigned = false): void
    {
        $unsignedStr = $unsigned ? ' UNSIGNED' : '';
        $this->columns[] = "`{$column}` BIGINT{$unsignedStr}";
    }

    public function smallInteger(string $column, bool $unsigned = false): void
    {
        $unsignedStr = $unsigned ? ' UNSIGNED' : '';
        $this->columns[] = "`{$column}` SMALLINT{$unsignedStr}";
    }

    public function tinyInteger(string $column, bool $unsigned = false): void
    {
        $unsignedStr = $unsigned ? ' UNSIGNED' : '';
        $this->columns[] = "`{$column}` TINYINT{$unsignedStr}";
    }

    public function float(string $column, int $precision = 8, int $scale = 2): void
    {
        $this->columns[] = "`{$column}` FLOAT({$precision}, {$scale})";
    }

    public function double(string $column, int $precision = 8, int $scale = 2): void
    {
        $this->columns[] = "`{$column}` DOUBLE({$precision}, {$scale})";
    }

    public function decimal(string $column, int $precision = 8, int $scale = 2): void
    {
        $this->columns[] = "`{$column}` DECIMAL({$precision}, {$scale})";
    }

    public function string(string $column, int $length = 255): void
    {
        $this->columns[] = "`{$column}` VARCHAR({$length})";
    }

    public function text(string $column): void
    {
        $this->columns[] = "`{$column}` TEXT";
    }

    public function mediumText(string $column): void
    {
        $this->columns[] = "`{$column}` MEDIUMTEXT";
    }

    public function longText(string $column): void
    {
        $this->columns[] = "`{$column}` LONGTEXT";
    }

    public function boolean(string $column): void
    {
        $this->columns[] = "`{$column}` TINYINT(1) NOT NULL DEFAULT 0";
    }

    public function date(string $column): void
    {
        $this->columns[] = "`{$column}` DATE";
    }

    public function dateTime(string $column): void
    {
        $this->columns[] = "`{$column}` DATETIME";
    }

    public function timestamp(string $column): void
    {
        $this->columns[] = "`{$column}` TIMESTAMP";
    }

    public function timestamps(): void
    {
        $this->dateTime('created_at')->nullable();
        $this->dateTime('updated_at')->nullable();
    }

    public function softDeletes(): void
    {
        $this->dateTime('deleted_at')->nullable();
    }

    public function json(string $column): void
    {
        $this->columns[] = "`{$column}` JSON";
    }

    public function uuid(string $column): void
    {
        $this->columns[] = "`{$column}` CHAR(36)";
    }

    public function nullable(): static
    {
        $last = array_pop($this->columns);
        $this->columns[] = $last . ' NULL';

        return $this;
    }

    public function default(mixed $value): static
    {
        $last = array_pop($this->columns);

        if (is_string($value)) {
            $this->columns[] = $last . " DEFAULT '{$value}'";
        } elseif (is_bool($value)) {
            $this->columns[] = $last . ' DEFAULT ' . ($value ? 'TRUE' : 'FALSE');
        } elseif (is_null($value)) {
            $this->columns[] = $last . ' DEFAULT NULL';
        } else {
            $this->columns[] = $last . " DEFAULT {$value}";
        }

        return $this;
    }

    public function index(string|array $columns): void
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $columnNames = implode('_', $columns);
        $this->indexes[] = "CREATE INDEX `idx_{$this->table}_{$columnNames}` ON `{$this->table}` (`" . implode('`, `', $columns) . "`)";
    }

    public function unique(string|array $columns): void
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $columnNames = implode('_', $columns);
        $this->uniques[] = "CREATE UNIQUE INDEX `uniq_{$this->table}_{$columnNames}` ON `{$this->table}` (`" . implode('`, `', $columns) . "`)";
    }

    public function primary(string|array $columns): void
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $this->columns[] = "PRIMARY KEY (`" . implode('`, `', $columns) . "`)";
    }

    public function foreign(string $column): ForeignKeyDefinition
    {
        return new ForeignKeyDefinition($this, $column);
    }

    public function addForeignKey(string $column, string $references, string $on): void
    {
        $this->foreignKeys[] = "ALTER TABLE `{$this->table}` ADD CONSTRAINT `fk_{$this->table}_{$column}` FOREIGN KEY (`{$column}`) REFERENCES `{$references}`(`id`)";
    }

    public function toSql(): array
    {
        $sqls = [];

        if ($this->create) {
            $columns = implode(', ', $this->columns);
            $sqls[] = "CREATE TABLE `{$this->table}` ({$columns}) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        } else {
            foreach ($this->columns as $column) {
                $columnParts = explode(' ', $column, 2);
                $columnName = trim($columnParts[0], '`');
                $columnDefinition = $columnParts[1] ?? '';

                $sqls[] = "ALTER TABLE `{$this->table}` ADD COLUMN `{$columnName}` {$columnDefinition}";
            }
        }

        foreach ($this->indexes as $index) {
            $sqls[] = $index;
        }

        foreach ($this->uniques as $unique) {
            $sqls[] = $unique;
        }

        foreach ($this->foreignKeys as $fk) {
            $sqls[] = $fk;
        }

        return $sqls;
    }
}
