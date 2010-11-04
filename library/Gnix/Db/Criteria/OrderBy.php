<?php
/**
 * Gnix_Db_Criteria_OrderBy
 *
 * @copyright   Copyright 2010, GMO Media, Inc. (http://www.gmo-media.jp)
 * @category    Gnix
 * @package     Gnix_Db
 * @license     http://www.gmo-media.jp/licence/mit.html   MIT License
 * @author      Chikara Miyake <chikara.miyake@gmo-media.jp>
 */
final class Gnix_Db_Criteria_OrderBy
{
    private $_columns = array();

    public function orderBy($column)
    {
        $this->_columns[] = $column;
    }

    public function orderByDesc($column)
    {
        $this->orderBy($column . ' DESC');
    }

    public function toString()
    {
        if ($this->_columns) {
            return ' ORDER BY ' . implode(', ', $this->_columns);
        }

        return '';
    }
}
