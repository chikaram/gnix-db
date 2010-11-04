<?php
/**
 * Gnix_Db_Query_Resolver
 *
 * @copyright   Copyright 2010, GMO Media, Inc. (http://www.gmo-media.jp)
 * @category    Gnix
 * @package     Gnix_Db
 * @license     http://www.gmo-media.jp/licence/mit.html   MIT License
 * @author      Chikara Miyake <chikara.miyake@gmo-media.jp>
 */
final class Gnix_Db_Query_Resolver
{
    private $_rowClass;
    private $_schema;
    private $_table;

    public function __construct($calledClass, $schema, $table)
    {
        if (!preg_match('/^((\w+)_(\w+))_Query$/', $calledClass, $matches)) {
            throw new Gnix_Db_Exception("Somthing wrong with the Query class '$calledClass'");
        }

        $this->_rowClass = $matches[1];
        $this->_schema   = $schema ? $schema : Gnix_Db_Util::uncamelize($matches[2]);
        $this->_table    = $table  ? $table  : Gnix_Db_Util::uncamelize($matches[3]);
    }

    public function getRowClass()
    {
        return $this->_rowClass;
    }

    public function getSchema()
    {
        return $this->_schema;
    }

    public function getTable()
    {
        return $this->_table;
    }
}
