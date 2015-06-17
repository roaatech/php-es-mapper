<?php

namespace ItvisionSy\EsMapper;

use ArrayAccess;
use Iterator;

/**
 * Description of Model
 *
 * @author muhannad
 */
class Model implements ArrayAccess, Iterator {

    protected $esHitData;
    protected $id;
    protected $type;
    protected $index;
    protected $score;
    protected $attributes = [];

    public static function MakeOfType(array $esHit, $className = "") {
        if ($className && strpos($className, '{type}') !== false) {
            $baseClassName = str_replace(' ', '', ucwords(preg_replace('/[^a-zA-Z0-9]+/', ' ', $esHit['_type'])));
            $fullClassName = str_replace("{type}", $baseClassName, $className);
            if (!class_exists($fullClassName)) {
                $fullClassName = static::class;
            }
        } else {
            $fullClassName = $className? : static::class;
        }
        return $fullClassName::make($esHit);
    }

    public static function make(array $esHit) {
        return new static($esHit);
    }

    public function __construct(array $esHit) {
        $this->esHitData = $esHit;
        $this->id = $esHit['_id'];
        $this->type = $esHit['_type'];
        $this->index = $esHit['_index'];
        $this->score = $esHit['_score'];
        $this->attributes = $esHit['_source'];
    }

    public function __get($name) {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : (property_exists($this, $name) ? $this->$name : null);
    }

    public function offsetExists($offset) {
        return array_key_exists($offset, $this->attributes);
    }

    public function offsetGet($offset) {
        return @$this->attributes[$offset];
    }

    public function offsetSet($offset, $value) {
        $this->attributes[$offset] = $value;
        return $this;
    }

    public function offsetUnset($offset) {
        unset($this->attributes[$offset]);
        return $this;
    }

    public function current() {
        return current($this->attributes);
    }

    public function key() {
        return key($this->attributes);
    }

    public function next() {
        return next($this->attributes);
    }

    public function rewind() {
        return rewind($this->attributes);
    }

    public function valid() {
        return valid($this->attributes);
    }

}
