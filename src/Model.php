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
use Elasticsearch\Client;
use Exception;
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
     * The es client object
     * @var Client
     */
    protected $esClient;

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
     * @param Client $esClient ElasticSearch client
     * @return Model
     */
    public static function MakeOfType(array $esHit, $className = "", Client $esClient = null) {
        if ($className && strpos($className, '{type}') !== false) {
            $baseClassName = str_replace(' ', '', ucwords(preg_replace('/[^a-zA-Z0-9]+/', ' ', $esHit['_type'])));
            $fullClassName = str_replace("{type}", $baseClassName, $className);
            if (!class_exists($fullClassName)) {
                $fullClassName = array_key_exists('_source', $esHit) ? static::class : ArrayObject::class;
            }
        } else {
            $fullClassName = $className? : static::class;
        }
        return $fullClassName::make($esHit, $esClient);
    }

    /**
     * A factory method to create a new class object for the provided hit data.
     * 
     * @param array $esHit
     * @param Client $esClient ElasticSearch client
     * @return Model
     */
    public static function make(array $esHit, Client $esClient = null) {
        return new static($esHit, $esClient);
    }

    /**
     * Constructor method
     * 
     * @param array $esHit
     * @param Client $esClient ElasticSearch client
     */
    public function __construct(array $esHit, Client $esClient = null) {
        $this->esHitData = $esHit;
        $this->esClient = $esClient;
        $source = array_key_exists("_source", $esHit) ? "_source" : "fields";
        $this->attributes = $esHit[$source];
        unset($esHit[$source]);
        foreach ($esHit as $key => $value) {
            $key = trim($key, "_");
            $this->meta[$key] = $value;
        }
    }

    public function __get($name) {
        if (substr($name, 0, 2) == '__') {
            $tName = substr($name, 1);
            return property_exists($this, $tName) ? $this->$tName : ($this->offsetExists($name) ? $this->attributes[$name] : (array_key_exists($tName, $this->meta) ? $this->meta[$tName] : null));
        } elseif (substr($name, 0, 1) == '_') {
            $tName = substr($name, 1);
            return array_key_exists($tName, $this->meta) ? $this->meta[$tName] : ($this->offsetExists($name) ? $this->attributes[$name] : (property_exists($this, $tName) ? $this->$tName : null));
        } else {
            return $this->offsetExists($name) ? $this->attributes[$name] : (array_key_exists($name, $this->meta) ? $this->meta[$name] : (property_exists($this, $name) ? $this->$name : null));
        }
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

    public function canAlter() {
        if (!array_key_exists('id', $this->meta)) {
            return false;
        }
        if (!array_key_exists('type', $this->meta)) {
            return false;
        }
        if (!array_key_exists('index', $this->meta)) {
            return false;
        }
        return true;
    }

    /**
     * Sets elasticsearch client
     * @param Client $esClient
     */
    public function setElasticClient(Client $esClient) {
        $this->esClient = $esClient;
    }

    /**
     * Returns elastic hit data (the original data provided).
     * 
     * @return array
     */
    public function getEsHitData() {
        return $this->esHitData;
    }

    /**
     * Return attributes (actual document data)
     * @return array
     */
    public function getAttributes() {
        $values = func_get_args();
        if (count($values) === 1) {
            $result = [];
            foreach ($values as $value) {
                $result[$value] = @$this->attributes[$value];
            }
            return $result;
        } elseif (count($values) > 1) {
            return @$this->attributes[$values[0]];
        } else {
            return $this->attributes;
        }
    }

    /**
     * Return meta data
     * @return array
     */
    public function getMeta($key = null) {
        if ($key) {
            return $this->meta[$key];
        }
        return $this->meta;
    }

    /**
     * ElasticSearch Client
     * @return Client|null
     */
    public function getElasticClient() {
        return $this->esClient;
    }

    /**
     * Updates the document
     * 
     * @param array $data the normal elastic update parameters to be used
     * @param array $parameters the parameters array
     * @return array
     * @throws Exception
     */
    public function update(array $data, array $parameters = []) {
        if (!$this->canAlter()) {
            throw new Exception('Need index, type, and key to update a document');
        }
        if (!$this->esClient) {
            throw new Exception('Need ElasticSearch client object to ALTER operations');
        }
        $params = [
            'index' => $this->meta['index'],
            'type' => $this->meta['type'],
            'id' => $this->meta['id'],
            'body' => $data] + $parameters;
        return $this->esClient->update($params);
    }

    /**
     * Deletes a document
     * 
     * @param array $parameters
     * @return array
     * @throws Exception
     */
    public function delete(array $parameters = []) {
        if (!$this->canAlter()) {
            throw new Exception('Need index, type, and key to update a document');
        }
        if (!$this->esClient) {
            throw new Exception('Need ElasticSearch client object to ALTER operations');
        }
        $params = [
            'index' => $this->meta['index'],
            'type' => $this->meta['type'],
            'id' => $this->meta['id']] + $parameters;
        $result = $this->esClient->delete($params);
        return $result;
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
