<?php
/**
 * Gnix_Db_Literal
 *
 * @copyright   Copyright 2010, GMO Media, Inc. (http://www.gmo-media.jp)
 * @category    Gnix
 * @package     Gnix_Db
 * @license     http://www.gmo-media.jp/licence/mit.html   MIT License
 * @author      Chikara Miyake <chikara.miyake@gmo-media.jp>
 */
final class Gnix_Db_Literal
{
    private $_literal;

    public function __construct($literal)
    {
        // TODO: Exception if $data is not a scalar value.
        $this->_literal = $literal;
    }

    public function toString()
    {
        return (string) $this->_literal;
    }
}
