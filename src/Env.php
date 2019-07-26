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
    private static $root_directory;

    public static function getRootDirectory() : ?string 
    {
        return self::$root_directory;
    }

    public static function toLocalPath(string $path) : string 
    {
        $local_path = self::$root_directory;

        if (strlen($local_path) > 0 && $local_path[strlen($local_path)-1] != DIRECTORY_SEPARATOR)
            $local_path .= DIRECTORY_SEPARATOR;

        $path = \str_replace("/", DIRECTORY_SEPARATOR, $path);
        $path = \str_replace("\\", DIRECTORY_SEPARATOR, $path);

        $local_path .= $path;

        $local_path = \str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $local_path);

        return $local_path;
    }

    /**
     * Load the environment properties from the .env file in the
     * root directory.
     *
     * @return void
     */
    public static function init(string $root_directory) : void
    {
        self::$root_directory = $root_directory;

        $envfile = $root_directory . DIRECTORY_SEPARATOR . ".env";

        if (!file_exists($envfile))
            return;

        $envs = parse_ini_file($envfile);
        foreach ($envs as $var => $value)
            self::put($var, $value);

        $config_path = Env::toLocalPath(Env::get("config.path") ?? "config");
        $config_provider = new \Gekko\Config\ConfigProvider(Env::get("config.driver") ?? "php", Env::get("config.env"), $config_path);

        $env_config = $config_provider->getConfig("env");
        $envs = $env_config->getKeys();

        if (!empty($envs))
        {
            foreach ($envs as $key)
                self::put($key, $env_config->get($key) ?? "");
        }
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
