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
use ItvisionSy\EsMapper\Model;
use ItvisionSy\EsMapper\Result;
use Elasticsearch\Client;

/**
 * A result-array container to manage mapping elements into correct model 
 * classes
 *
 * @package ItvisionSy\EsMapper
 * @author Muhannad Shelleh <muhannad.shelleh@itvision-sy.com>
 */
class Result implements ArrayAccess, Iterator {

    /**
     * The result set
     * @var array
     */
    protected $result;

    /**
     * The es client object
     * @var Client
     */
    protected $esClient;

    /**
     * Current element index for current/valid/next/... operations
     * @var integer
     */
    protected $currentIndex = 0;

    /**
     * The class name/pattern for the models when created
     * @var string
     */
    protected $modelClass = '';

    /**
     * A boolean defines whether to return the document id or the array index
     * as keys.
     * 
     * @var boolean
     */
    protected $indexKeys = false;

    /**
     * Factory method to create the result object
     * 
     * @param array $result
     * @param Client $esClient
     * @return Result
     */
    public static function make(array $result, Client $esClient = null) {
        return new static($result, $esClient);
    }

    /**
     * Constructor method to create the result object
     * 
     * @param array $result
     * @param Client $esClient
     */
    public function __construct(array $result, Client $esClient = null) {
        $this->result = $result;
        $this->esClient = $esClient;
    }

    /**
     * Sets the model class name. When initating a new model object this name
     * will be used.
     * 
     * @param string $class
     * @return Result
     */
    public function setModelClass($class) {
        $this->modelClass = $class;
        return $this;
    }

    /**
     * Sets the result to return array index key as the key for key mehtod.
     * 
     * @return Result
     */
    public function useIndexKeys() {
        $this->indexKeys = true;
        return $this;
    }

    /**
     * Sets the result to return document id as the key for the key method
     * 
     * @return Result
     */
    public function useDocumentKeys() {
        $this->indexKeys = false;
        return $this;
    }

    /**
     * Returns the number of the hits
     * 
     * @return integer
     */
    public function count() {
        return $this->result['hits']['total'];
    }

    /**
     * Returns the highest score in the hists
     * 
     * @return float
     */
    public function score() {
        return $this->result['hits']['max_score'];
    }

    /**
     * Returns the raw data array in the hist.
     * This is the raw returned hits list from ES.
     * 
     * @return array
     */
    public function data() {
        return $this->result['hits']['hits'];
    }

    /**
     * Returns the current model object in the current offset.
     * 
     * @return Model
     */
    public function current() {
        return $this->offsetGet($this->currentIndex);
    }

    /**
     * Returns the current array index or document object at the offset.
     * 
     * @see Result::useIndexKeys() 
     * @see Result::useDocumentKeys()
     * @return mixed
     */
    public function key() {
        if ($this->indexKeys) {
            return $this->currentIndex;
        } else {
            return $this->result['hits']['hits'][$this->currentIndex]['_id'];
        }
    }

    /**
     * Moves the internal current offset to the next element in the array.
     */
    public function next() {
        ++$this->currentIndex;
    }

    /**
     * Resets the internal current offset to 0 (the beginning).
     */
    public function rewind() {
        $this->currentIndex = 0;
    }

    /**
     * Returns true if the current offset index exists, false otherwise.
     * 
     * @return boolean
     */
    public function valid() {
        return $this->offsetExists($this->currentIndex);
    }

    /**
     * Returns true if the $offset exsits in the results array, false otherwise.
     * IT uses the array index keys for the offset.
     * 
     * @param integer $offset
     * @return boolean
     */
    public function offsetExists($offset) {
        return array_key_exists($offset, $this->result['hits']['hits']);
    }

    /**
     * Gets the document object in the $offset
     * 
     * @param integer $offset
     * @return Model
     */
    public function offsetGet($offset) {
        return Model::makeOfType($this->result['hits']['hits'][$offset], $this->modelClass, $this->esClient);
    }

    /**
     * Not in use. Throws error.
     */
    public function offsetSet($offset, $value) {
        trigger_error('Can not set offset in ES results', E_USER_ERROR);
    }

    /**
     * Not in use. Throws error.
     */
    public function offsetUnset($offset) {
        trigger_error('Can not unset offset in ES results', E_USER_ERROR);
    }

    /**
     * Returns the first document object in the results array.
     * 
     * @return Model
     */
    public function first() {
        if ($this->offsetExists(0)) {
            return $this->offsetGet(0);
        } else {
            trigger_error('Can not get null of empty result set', E_USER_WARNING);
            return null;
        }
    }

    /**
     * Gets specific value from within the result set using dot notation
     * 
     * The path should be absolution from the beginning, and include the normal 
     * elastic search structure depending on the query type
     * 
     * @param string $path
     */
    public function fetch($path) {
        $result = $this->result;
        $keys = explode(".", $path);
        foreach ($keys as $key) {
            if (!array_key_exists($key, $result)) {
                return null;
            }
            $result = $result[$key];
        }
        return $result;
    }

    public function __get($name) {
        return $this->fetch($name);
    }

}
