<?php
/*
 * (c) Leonardo Brugnara
 *
 * Full copyright and license information in LICENSE file.
 */
namespace Gekko;

class Env 
{
    /**
     * @var string?
     */
    private static $rootDir;

    public static function rootDir() : ?string 
    {
        return self::$rootDir;
    }

    /**
     * Load the environment properties from the .env file in the
     * root directory.
     *
     * @return void
     */
    public static function init(string $rootDir) : void
    {
        self::$rootDir = $rootDir;

        $envfile = $rootDir . DIRECTORY_SEPARATOR . ".env";

        if (!file_exists($envfile))
            return;

        $envs = parse_ini_file($envfile);
        foreach ($envs as $var => $value)
            self::put($var, $value);
    }

    /**
     * Set a new env variable
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public static function put(string $key, string $value) : void
    {
        if (is_array($value))
        {
            foreach ($value as $vkey => $vvalue)
                self::put("{$key}[$vkey]", $vvalue);
            $_ENV[$key] = $value;
        }
        else
        {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }

    /**
     * Retrieve an env variable
     *
     * @param string $key
     * @return string?
     */
    public static function get(string $key) : ?string
    {
        return isset($_ENV[$key]) ? $_ENV[$key] : null;
    }
}
