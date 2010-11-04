<?php
/**
 * Gnix_Db_Criteria_Limit
 *
 * @copyright   Copyright 2010, GMO Media, Inc. (http://www.gmo-media.jp)
 * @category    Gnix
 * @package     Gnix_Db
 * @license     http://www.gmo-media.jp/licence/mit.html   MIT License
 * @author      Chikara Miyake <chikara.miyake@gmo-media.jp>
 */
final class Gnix_Db_Criteria_Limit
{
    private $_limit;
    private $_offset;

    public function limit($limit)
    {
        $this->_limit = $limit;
    }

    public function offset($offset)
    {
        if ($this->_limit === null) {
            throw new Gnix_Db_Exception("Can't set 'offset' before you set 'limit'");
        }

        $this->_offset = $offset;
    }

    public function page($page)
    {
        if ($this->_limit === null) {
            throw new Gnix_Db_Exception("Can't set 'offset' before you set 'limit'");
        }

        $page = (int) $page;
        if ($page <= 0) {
            $page = 1;
        }

        $this->_offset = ($page - 1) * $this->_limit;
    }

    public function toString()
    {
        if ($this->_limit === null) {
            return '';
        }

        if ($this->_offset) {
            return ' LIMIT ' . (int) $this->_offset . ', ' . (int) $this->_limit;
        }

        return ' LIMIT ' . (int) $this->_limit;
    }
}
