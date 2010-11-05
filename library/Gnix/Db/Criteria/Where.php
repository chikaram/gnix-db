<?php
/**
 * Gnix_Db_Criteria_Where
 *
 * @copyright   Copyright 2010, GMO Media, Inc. (http://www.gmo-media.jp)
 * @category    Gnix
 * @package     Gnix_Db
 * @license     http://www.gmo-media.jp/licence/mit.html   MIT License
 * @author      Chikara Miyake <chikara.miyake@gmo-media.jp>
 */
final class Gnix_Db_Criteria_Where
{
    private $_columns = array();
    private $_params  = array();

    public function where($column, $params = null)
    {
        $this->_columns[] = $column;

        foreach ((array) $params as $param) {
            $this->_params[] = $param;
        }
    }

    public function whereEqual($column, $param)
    {
        $this->where($column . ' = ?', $param);
    }

    public function whereNotEqual($column, $param)
    {
        $this->where($column . ' != ?', $param);
    }

    public function whereGreater($column, $param)
    {
        $this->where($column . ' > ?', $param);
    }

    public function whereGreaterEqual($column, $param)
    {
        $this->where($column . ' >= ?', $param);
    }

    public function whereLess($column, $param)
    {
        $this->where($column . ' < ?', $param);
    }

    public function whereLessEqual($column, $param)
    {
        $this->where($column . ' <= ?', $param);
    }

    public function whereIsNull($column)
    {
        $this->where($column . ' IS NULL');
    }

    public function whereIsNotNull($column)
    {
        $this->where($column . ' IS NOT NULL');
    }

    public function whereLike($column, $param)
    {
        $this->where($column . ' LIKE ?', $param);
    }

    public function whereNotLike($column, $param)
    {
        $this->where($column . ' NOT LIKE ?', $param);
    }

    public function whereBetween($column, $paramFrom, $paramTo)
    {
        $this->where($column . ' BETWEEN ? AND ?', array($paramFrom, $paramTo));
    }

    public function whereIn($column, array $params)
    {
        $this->where($column . ' IN (' . $this->_getHolderString(count($params)) . ')', $params);
    }

    public function whereNotIn($column, array $params)
    {
        $this->where($column . ' NOT IN (' . $this->_getHolderString(count($params)) . ')', $params);
    }

    public function toString()
    {
        if ($this->_columns) {
            return ' WHERE ' . implode(' AND ', $this->_columns);
        }
        return '';
    }

    public function getParams()
    {
        return $this->_params;
    }

    private function _getHolderString($num)
    {
        return implode(', ', array_fill(0, $num, '?'));
    }
}
