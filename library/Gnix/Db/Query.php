<?php
/**
 * Gnix_Db_Query
 *
 * @copyright   Copyright 2010, GMO Media, Inc. (http://www.gmo-media.jp)
 * @category    Gnix
 * @package     Gnix_Db
 * @license     http://www.gmo-media.jp/licence/mit.html   MIT License
 * @author      Chikara Miyake <chikara.miyake@gmo-media.jp>
 */
abstract class Gnix_Db_Query
{
    protected static $_schema;
    protected static $_table;
    protected static $_key = 'id';

    protected static function _getCriteria()
    {
        return new Gnix_Db_Criteria();
    }

    /**
     * CREATE method
     */
    public static function create(array $data)
    {
        $columns = array();
        $holders = array();
        $params  = array();
        foreach ($data as $key => $value) {
            $columns[] = $key;
            if ($value instanceof Gnix_Db_Literal) {
                $holders[] = $value->toString();
            } else {
                $holders[] = '?';
                $params[] = $value;
            }
        }

        $resolver = self::_getResolver();
        $sql = 'INSERT INTO ' . $resolver->getTable() . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $holders) . ')';

        $dbh = Gnix_Db_Connection_Master::get($resolver->getSchema());
        $sth = $dbh->prepare($sql);
        $sth->execute($params);
        return $dbh->lastInsertId();
    }

    /**
     * FIND methods
     */
    public static function findAll(Gnix_Db_Criteria $criteria, array $columns = array('*'), $master = false)
    {
        $resolver = self::_getResolver();
        $sql = 'SELECT ' . implode(', ', $columns) . ' FROM ' . $resolver->getTable() . $criteria->assemble();

        if ($master) {
            $dbh = Gnix_Db_Connection_Master::get($resolver->getSchema());
        } else {
            $dbh = Gnix_Db_Connection_Slave::get($resolver->getSchema());
        }
        $sth = $dbh->prepare($sql);
        $sth->execute($criteria->getParams());
        return self::_hydrate($resolver->getRowClass(), $sth->fetchAll());
    }

    public static function find(Gnix_Db_Criteria $criteria, array $columns = array('*'), $master = false)
    {
        $rowObjects = self::findAll($criteria, $columns, $master);
        return isset($rowObjects[0]) ? $rowObjects[0] : null;
    }

    public static function findByKey($key, array $columns = array('*'), $master = false)
    {
        $criteria = self::_getCriteriaByKey($key);
        return self::find($criteria, $columns, $master);
    }

    public static function count(Gnix_Db_Criteria $criteria, $master = false)
    {
        $rowObject = self::find($criteria, array('COUNT(*) AS count'), $master);
        return (int) $rowObject->getCount();
    }

    /**
     * UPDATE methods
     */
    public static function update(array $data, Gnix_Db_Criteria $criteria)
    {
        $holders = array();
        $params  = array();
        foreach ($data as $key => $value) {
            if ($value instanceof Gnix_Db_Literal) {
                $holders[] = $key . ' = ' . $value->toString();
            } else {
                $holders[] = $key . ' = ?';
                $params[] = $value;
            }
        }

        $resolver = self::_getResolver();
        $sql = 'UPDATE ' . $resolver->getTable() . ' SET ' . implode(', ', $holders) . $criteria->assemble();

        $dbh = Gnix_Db_Connection_Master::get($resolver->getSchema());
        $sth = $dbh->prepare($sql);
        $sth->execute(array_merge($params, $criteria->getParams()));
        return $sth->rowCount();
    }

    public static function updateByKey(array $data, $key)
    {
        $criteria = self::_getCriteriaByKey($key);
        return self::update($data, $criteria);
    }

    /**
     * DELETE methods
     */
    public static function delete(Gnix_Db_Criteria $criteria)
    {
        $resolver = self::_getResolver();
        $sql = 'DELETE FROM ' . $resolver->getTable() . $criteria->assemble();

        $dbh = Gnix_Db_Connection_Master::get($resolver->getSchema());
        $sth = $dbh->prepare($sql);
        $sth->execute($criteria->getParams());
        return $sth->rowCount();
    }

    public static function deleteByKey($key)
    {
        $criteria = self::_getCriteriaByKey($key);
        return self::delete($criteria);
    }

    /**
     * Other methods
     */
    private static function _getResolver()
    {
        return new Gnix_Db_Query_Resolver(get_called_class(), static::$_schema, static::$_table);
    }

    protected static function _getCriteriaByKey($key)
    {
        return self::_getCriteria()
            ->whereEqual(static::$_key, $key)
        ;
    }

    private static function _hydrate($rowClass, array $rows)
    {
        $rowObjects = array();
        foreach ($rows as $row) {
            $rowObjects[] = new $rowClass($row);
        }
        return $rowObjects;
    }

    public static function getKeyName()
    {
        return static::$_key;
    }
}
