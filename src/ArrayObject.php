<?php

namespace ItvisionSy\EsMapper;

/**
 * A simple model holder which is an implemnetation of the ArrayObject PHP class
 *
 * @author Muhannad Shelleh <muhannad.shelleh@itvision-sy.com>
 */
class ArrayObject extends \ArrayObject implements IModel {

    protected $data;

    public function current() {
        return current($this->data);
    }

    public function key() {
        return key($this->data);
    }

    public function next() {
        return next($this->data);
    }

    public function rewind() {
        return rewind($this->data);
    }

    public function valid() {
        return valid($this->data);
    }

    public static function make(array $array) {
        $this->data = $array;
    }

}
