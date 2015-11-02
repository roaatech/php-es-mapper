<?php

namespace ItvisionSy\EsMapper;

use ArrayAccess;
use Iterator;

/**
 * Interface for any result model class
 * 
 * @author Muhannad Shelleh <muhannad.shelleh@itvision-sy.com>
 */
interface IModel extends ArrayAccess, Iterator{
    public static function make(array $array);
}
