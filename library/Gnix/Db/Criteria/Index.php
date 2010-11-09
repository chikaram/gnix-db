<?php
/**
 * Gnix_Db_Criteria_Index
 *
 * @copyright   Copyright 2010, GMO Media, Inc. (http://www.gmo-media.jp)
 * @category    Gnix
 * @package     Gnix_Db
 * @license     http://www.gmo-media.jp/licence/mit.html   MIT License
 * @author      Chikara Miyake <chikara.miyake@gmo-media.jp>
 */
final class Gnix_Db_Criteria_Index
{
    private $_hint = '';

    public function indexUse($index)
    {
        $this->_hint = ' USE INDEX (' . $index . ')';
    }

    public function indexForce($index)
    {
        $this->_hint = ' FORCE INDEX (' . $index . ')';
    }

    public function toString()
    {
        return $this->_hint;
    }
}
