<?php

/**
 * Copyright (c) 2015, Muhannad Shelleh
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN 
 * THE SOFTWARE.
 */

namespace ItvisionSy\EsMapper;

use ArrayAccess;
use Iterator;

/**
 * A document model class returned from ES find/query methods
 *
 * @package ItvisionSy\EsMapper
 * @author Muhannad Shelleh <muhannad.shelleh@itvision-sy.com>
 * 
 */
class Model implements IModel, ArrayAccess, Iterator {

    /**
     * The raw hit element array returned from ES
     * @var array
     */
    protected $esHitData;

    /**
     * Meta information about the model
     * @var array contains the following (not always):
     * @var mixed id The id of the document.
     * @var string type The type of the document.
     * @var string index The index of the document.
     * @var float score The score of the hit.
     */
    protected $meta = [];

    /**
     * The data array from the raw
     * @var array
     */
    protected $attributes = [];

    /**
     * A custom factory method that will try to detect the correct model class 
     * and instantiate and object of it.
     * 
     * @param array $esHit
     * @param string $className a class name or pattern.
     * @return Model
     */
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

    /**
     * A factory method to create a new class object for the provided hit data.
     * 
     * @param array $esHit
     * @return Model
     */
    public static function make(array $esHit) {
        return new static($esHit);
    }

    /**
     * Constructor method
     * 
     * @param array $esHit
     */
    public function __construct(array $esHit) {
        $this->esHitData = $esHit;
        $source = array_key_exists("_source", $esHit) ? "_source" : "fields";
        $this->attributes = $esHit[$source];
        unset($esHit[$source]);
        foreach ($esHit as $key => $value) {
            $key = trim($key, "_");
            $this->meta[$key] = $value;
        }
    }

    public function __get($name) {
        return $this->offsetExists($name) ? $this->attributes[$name] : (array_key_exists($name, $this->meta) ? $this->meta[$name] : (property_exists($this, $name) ? $this->$name : null));
    }

    public function __set($name, $value) {
        $this->offsetSet($name, $value);
    }

    /**
     * Returns true if there is an attribute named $offset, false otherwise
     * 
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset) {
        return array_key_exists($offset, $this->attributes);
    }

    /**
     * Returns the attribute specified in $offset.
     * 
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        return @$this->attributes[$offset];
    }

    /**
     * Sets the attribute name of the $offset
     * 
     * @param string $offset
     * @param mixed $value
     * @return Model
     */
    public function offsetSet($offset, $value) {
        $this->attributes[$offset] = $value;
        return $this;
    }

    /**
     * 
     * @param type $offset
     * @return Model
     */
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
