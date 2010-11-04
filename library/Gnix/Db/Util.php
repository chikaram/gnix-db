<?php
/**
 * Gnix_Db_Util
 *
 * @copyright   Copyright 2010, GMO Media, Inc. (http://www.gmo-media.jp)
 * @category    Gnix
 * @package     Gnix_Db
 * @license     http://www.gmo-media.jp/licence/mit.html   MIT License
 * @author      Chikara Miyake <chikara.miyake@gmo-media.jp>
 */
final class Gnix_Db_Util
{
    public static function uncamelize($str)
    {
        $str = lcfirst($str);
        $str = str_replace('_', '-', $str);
        $str = preg_replace('/([A-Z])/', '_$1', $str);
        return strtolower($str);
    }
}
