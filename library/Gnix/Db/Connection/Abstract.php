<?php
/**
 * Gnix_Db_Connection_Abstract
 *
 * @copyright   Copyright 2010, GMO Media, Inc. (http://www.gmo-media.jp)
 * @category    Gnix
 * @package     Gnix_Db
 * @license     http://www.gmo-media.jp/licence/mit.html   MIT License
 * @author      Chikara Miyake <chikara.miyake@gmo-media.jp>
 */
abstract class Gnix_Db_Connection_Abstract
{
    public static function setDefaultAttributes(array $defaultAttributes)
    {
        static::$_defaultAttributes = $defaultAttributes;
    }

    public static function setInfo($key, array $info)
    {
        if (!array_key_exists('host', $info)) {
            throw new Gnix_Db_Exception("'host' parameter is requisite for '$key'.");
        }

        if (!array_key_exists('port', $info)) {
            $info['port'] = '';
        }

        if (!array_key_exists('dbname', $info)) {
            throw new Gnix_Db_Exception("'dbname' parameter is requisite for '$key'.");
        }

        if (!array_key_exists('user', $info)) {
            throw new Gnix_Db_Exception("'user' parameter is requisite for '$key'.");
        }

        if (!array_key_exists('pass', $info)) {
            throw new Gnix_Db_Exception("'pass' parameter is requisite for '$key'.");
        }

        if (!array_key_exists('attributes', $info)) {
            $info['attributes'] = static::$_defaultAttributes;
        }

        static::$_infos[$key] = $info;
    }

    public static function getInfo($key)
    {
        if (array_key_exists($key, static::$_infos)) {
            return static::$_infos[$key];
        }

        throw new Gnix_Db_Exception("Information for '$key' is not set yet at " . get_called_class() . '.');
    }

    public static function get($key)
    {
        if (array_key_exists($key, static::$_connections)) {
            return static::$_connections[$key];
        }

        if (Gnix_Db_Connection_Master::getInfo($key) === Gnix_Db_Connection_Slave::getInfo($key)) {
            if (get_called_class() === 'Gnix_Db_Connection_Slave') {
                return Gnix_Db_Connection_Master::get($key);
            }
        }

        static::$_connections[$key] = self::_getConnection(self::getInfo($key));
        return static::$_connections[$key];
    }

    private static function _getConnection($info)
    {
        $connection = new PDO(sprintf('mysql:host=%s;port=%s;dbname=%s', $info['host'], $info['port'], $info['dbname']), $info['user'], $info['pass']);
        foreach ($info['attributes'] as $key => $value) {
            $connection->setAttribute($key, $value);
        }
        return $connection;
    }

    public static function disconnect($key)
    {
        unset(static::$_connections[$key]);
    }

    public static function disconnectAll()
    {
        static::$_connections = array();
    }
}
