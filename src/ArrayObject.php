<?php

namespace ItvisionSy\EsMapper;

use ArrayObject as PHPArrayObject;

/**
 * A simple model holder which is an implemnetation of the ArrayObject PHP class
 *
 * @author Muhannad Shelleh <muhannad.shelleh@itvision-sy.com>
 */
class ArrayObject extends PHPArrayObject implements IModel {

    public static function make(array $array) {
        return new static($array);
    }

}
