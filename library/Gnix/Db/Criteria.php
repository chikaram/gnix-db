<?php
/**
 * Gnix_Db_Criteria
 *
 * @copyright   Copyright 2010, GMO Media, Inc. (http://www.gmo-media.jp)
 * @category    Gnix
 * @package     Gnix_Db
 * @license     http://www.gmo-media.jp/licence/mit.html   MIT License
 * @author      Chikara Miyake <chikara.miyake@gmo-media.jp>
 */
final class Gnix_Db_Criteria
{
    private $_where;
    private $_orderBy;
    private $_limit;

    public static function self()
    {
        trigger_error('Gnix_Db_Criteria::self() is deprecated. Use self::_getCriteria() in *_Query class instead', E_USER_DEPRECATED);
        return new self();
    }

    public function __construct()
    {
        $this->_where   = new Gnix_Db_Criteria_Where();
        $this->_orderBy = new Gnix_Db_Criteria_OrderBy();
        $this->_limit   = new Gnix_Db_Criteria_Limit();
    }

    public function __call($name, array $arguments)
    {
        switch ($name) {
            case 'where':
            case 'whereEqual':
            case 'whereNotEqual':
            case 'whereGreater':
            case 'whereGreaterEqual':
            case 'whereLess':
            case 'whereLessEqual':
            case 'whereIsNull':
            case 'whereIsNotNull':
            case 'whereLike':
            case 'whereNotLike':
            case 'whereBetween':
            case 'whereIn':
            case 'whereNotIn':
                call_user_func_array(array($this->_where, $name), $arguments);
                return $this;
            case 'orderBy':
            case 'orderByDesc':
                call_user_func_array(array($this->_orderBy, $name), $arguments);
                return $this;
            case 'limit':
            case 'offset':
            case 'page':
                call_user_func_array(array($this->_limit, $name), $arguments);
                return $this;
        }

        throw new Exception('Call to undefined method ' . __CLASS__ . '::' . $name . '()');
    }

    public function assemble()
    {
        return $this->_where->toString() . $this->_orderBy->toString() . $this->_limit->toString();
    }

    public function getParams()
    {
        return $this->_where->getParams();
    }

    public function debug()
    {
        ob_start();
        echo 'SQL: ' . $this->assemble() . PHP_EOL;
        echo ' -- ' . PHP_EOL;
        echo 'PARAMS: ';
        var_dump($this->_where->getParams());
        return ob_get_clean();
    }
}
