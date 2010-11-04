<?php
/**
 * Gnix_Db_Connection_Slave
 *
 * @copyright   Copyright 2010, GMO Media, Inc. (http://www.gmo-media.jp)
 * @category    Gnix
 * @package     Gnix_Db
 * @license     http://www.gmo-media.jp/licence/mit.html   MIT License
 * @author      Chikara Miyake <chikara.miyake@gmo-media.jp>
 */
final class Gnix_Db_Connection_Slave extends Gnix_Db_Connection_Abstract
{
    protected static $_defaultAttributes = array();
    protected static $_infos             = array();
    protected static $_connections       = array();
}
