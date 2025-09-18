<?php

declare(strict_types=1);

namespace App\Config;

class Config
{
    private static array $config = [];

    public static function load(string $configPath): void
    {
        if (!file_exists($configPath)) {
            throw new \InvalidArgumentException("Configuration file not found: {$configPath}");
        }

        $config = require $configPath;
        if (!is_array($config)) {
            throw new \InvalidArgumentException("Configuration file must return an array");
        }

        self::$config = $config;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public static function set(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $config = &self::$config;

        foreach ($keys as $k) {
            if (!is_array($config)) {
                $config = [];
            }
            if (!array_key_exists($k, $config)) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config = $value;
    }

    public static function all(): array
    {
        return self::$config;
    }
}