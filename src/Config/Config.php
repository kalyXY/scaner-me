<?php

declare(strict_types=1);

namespace App\Config;

class Config
{
    /**
     * Retourne une valeur de configuration basée sur les constantes définies
     */
    public static function get(string $key, $default = null)
    {
        switch ($key) {
            case 'app.name':
                return APP_NAME;
            case 'app.version':
                return APP_VERSION;
            case 'app.environment':
                return APP_ENV;
            case 'database.host':
                return DB_HOST;
            case 'database.port':
                return DB_PORT;
            case 'database.database':
                return DB_NAME;
            case 'database.username':
                return DB_USER;
            case 'database.password':
                return DB_PASS;
            case 'database.charset':
                return DB_CHARSET;
            default:
                return $default;
        }
    }

    /**
     * Retourne toute la configuration sous forme de tableau
     */
    public static function all(): array
    {
        return [
            'app' => [
                'name' => APP_NAME,
                'version' => APP_VERSION,
                'environment' => APP_ENV,
            ],
            'database' => [
                'host' => DB_HOST,
                'port' => DB_PORT,
                'database' => DB_NAME,
                'username' => DB_USER,
                'password' => DB_PASS,
                'charset' => DB_CHARSET,
            ]
        ];
    }
}