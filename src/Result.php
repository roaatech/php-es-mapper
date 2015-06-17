<?php

namespace ItvisionSy\EsMapper;

use ArrayAccess;
use Iterator;
use ItvisionSy\EsMapper\Model;
use ItvisionSy\EsMapper\Result;

class Result implements ArrayAccess, Iterator {

    protected $result;
    protected $currentIndex = 0;
    protected $modelClass = '';
    protected $indexKeys = false;

    /**
     * 
     * @param array $result
     * @return Result
     */
    public static function make(array $result) {
        return new static($result);
    }

    public function __construct(array $result) {
        $this->result = $result;
    }

    public function setModelClass($class) {
        $this->modelClass = $class;
        return $this;
    }

    public function useIndexKeys() {
        $this->indexKeys = true;
        return false;
    }

    public function useDocumentKeys() {
        $this->indexKeys = false;
        return false;
    }

    public function count() {
        return $this->result['hits']['total'];
    }

    public function score() {
        return $this->result['hits']['max_score'];
    }

    public function data() {
        return $this->result['hits']['hits'];
    }

    public function current() {
        return $this->offsetGet($this->currentIndex);
    }

    public function key() {
        if ($this->indexKeys) {
            return $this->currentIndex;
        } else {
            return $this->result['hits']['hits'][$this->currentIndex]['_id'];
        }
    }

    public function next() {
        ++$this->currentIndex;
    }

    public function rewind() {
        $this->currentIndex = 0;
    }

    public function valid() {
        return $this->offsetExists($this->currentIndex);
    }

    public function offsetExists($offset) {
        return array_key_exists($offset, $this->result['hits']['hits']);
    }

    public function offsetGet($offset) {
        return Model::makeOfType($this->result['hits']['hits'][$offset], $this->modelClass);
    }

    public function offsetSet($offset, $value) {
        trigger_error('Can not set offset in ES results', E_USER_ERROR);
    }

    public function offsetUnset($offset) {
        trigger_error('Can not unset offset in ES results', E_USER_ERROR);
    }

    public function first() {
        return $this->offsetGet(0);
    }

}
