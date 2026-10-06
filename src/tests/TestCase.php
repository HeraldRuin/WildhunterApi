<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PDO;

abstract class TestCase extends BaseTestCase
{
    /**
     * Провайдеры модулей при загрузке читают core_settings.
     * Файл SQLite создаётся до bootstrap, чтобы эта таблица уже была.
     */
    public function createApplication()
    {
        $this->prepareSqliteDatabase();

        return parent::createApplication();
    }

    private function prepareSqliteDatabase(): void
    {
        $directory = dirname(__DIR__).'/storage/framework/testing';

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $database = $directory.'/testing.sqlite';

        if (!file_exists($database)) {
            touch($database);
        }

        $pdo = new PDO('sqlite:'.$database);
        $pdo->exec('CREATE TABLE IF NOT EXISTS core_settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NULL,
            "group" VARCHAR(50) NULL,
            val TEXT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL
        )');

        foreach ([
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => $database,
            'DB_URL' => '',
        ] as $key => $value) {
            putenv($key.'='.$value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}
