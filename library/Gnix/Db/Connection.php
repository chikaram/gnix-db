<?php
/**
 * Gnix_Db_Connection
 *
 * @copyright   Copyright 2010, GMO Media, Inc. (http://www.gmo-media.jp)
 * @category    Gnix
 * @package     Gnix_Db
 * @license     http://www.gmo-media.jp/licence/mit.html   MIT License
 * @author      Chikara Miyake <chikara.miyake@gmo-media.jp>
 */
final class Gnix_Db_Connection
{
    public static function setDefaultAttributes(array $attributes)
    {
        Gnix_Db_Connection_Master::setDefaultAttributes($attributes);
        Gnix_Db_Connection_Slave::setDefaultAttributes($attributes);
    }

    public static function setInfo($key, array $info)
    {
        Gnix_Db_Connection_Master::setInfo($key, $info);
        Gnix_Db_Connection_Slave::setInfo($key, $info);
    }

    public static function disconnect($key)
    {
        Gnix_Db_Connection_Master::disconnect($key);
        Gnix_Db_Connection_Slave::disconnect($key);
    }

    public static function disconnectAll()
    {
        Gnix_Db_Connection_Master::disconnectAll();
        Gnix_Db_Connection_Slave::disconnectAll();
    }
}
