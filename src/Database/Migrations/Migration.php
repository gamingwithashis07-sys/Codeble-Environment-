<?php

declare(strict_types=1);

namespace LoveGem\Database\Migrations;

abstract class Migration
{
    abstract public function up(): void;

    abstract public function down(): void;

    protected function create(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        $this->execute($blueprint);
    }

    protected function table(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table, true);
        $callback($blueprint);
        $this->execute($blueprint);
    }

    protected function drop(string $table): void
    {
        $sql = "DROP TABLE IF EXISTS `{$table}`";
        $this->getConnection()->raw($sql);
    }

    protected function dropIfExists(string $table): void
    {
        $sql = "DROP TABLE IF EXISTS `{$table}`";
        $this->getConnection()->raw($sql);
    }

    protected function getConnection(): \PDO
    {
        return app('db')->getConnection();
    }

    protected function execute(Blueprint $blueprint): void
    {
        $sql = $blueprint->toSql();
        $this->getConnection()->raw($sql);
    }
}
