<?php
/**
 * Gnix_Db_Row
 *
 * @copyright   Copyright 2010, GMO Media, Inc. (http://www.gmo-media.jp)
 * @category    Gnix
 * @package     Gnix_Db
 * @license     http://www.gmo-media.jp/licence/mit.html   MIT License
 * @author      Chikara Miyake <chikara.miyake@gmo-media.jp>
 */
abstract class Gnix_Db_Row
{
    private static $_cache = array();
    private $_row = array();

    public function __construct(array $row = array())
    {
        $this->_row = $row;
    }

    public function __call($method, array $arguments)
    {
        if (!array_key_exists($method, self::$_cache)) {
            self::$_cache[$method] = self::_parse($method);
        }

        $prefix = self::$_cache[$method]['prefix'];
        $column = self::$_cache[$method]['column'];

        switch ($prefix) {
            case 'get':
                if (array_key_exists($column, $this->_row)) {
                    return $this->_row[$column];
                }
                break;
            case 'set':
                if (array_key_exists(0, $arguments)) {
                    $this->_row[$column] = $arguments[0];
                    return;
                }
       }

        throw new Gnix_Db_Exception('Call to undefined method ' . get_class($this) . '::' . $method . '()');
    }

    private static function _parse($method)
    {
        if (preg_match('/^(get|set)([A-Z]\w*)$/', $method, $matches)) {
            return array(
                'prefix' => $matches[1],
                'column' => Gnix_Db_Util::uncamelize($matches[2]),
            );
        }

        throw new Gnix_Db_Exception('Call to undefined method ' . get_class($this) . '::' . $method . '()');
    }

    public function save($findAfterCreate = true)
    {
        $keyName = call_user_func(array($this->_getQueryClass(), 'getKeyName'));

        // UPDATE if there is Primary Key data.
        if (array_key_exists($keyName, $this->_row)) {
            call_user_func(array($this->_getQueryClass(), 'updateByKey'), $this->_row, $this->_row[$keyName]);
            return;
        }

        // INSERT if there is NOT Primary Key data.
        $key = call_user_func(array($this->_getQueryClass(), 'create'), $this->_row);
        if ($findAfterCreate) {
            $rowObject = call_user_func(array($this->_getQueryClass(), 'findByKeyOnMaster'), $key);
            if (!isset($rowObject->_row)) {
                // Couldn't get data just after inserting it. This couldn't be possible!
                throw new Gnix_Db_Exception("Can't get data 'PRIMARY KEY = $key' from " . get_class($this) . 'master db.');
            }
            $this->_row = $rowObject->_row;
        }
        return $key;
    }

    public function delete()
    {
        $keyName = call_user_func(array($this->_getQueryClass(), 'getKeyName'));
        call_user_func(array($this->_getQueryClass(), 'deleteByKey'), $this->_row[$keyName]);
    }

    private function _getQueryClass()
    {
        return get_class($this) . '_Query';
    }
}
