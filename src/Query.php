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

use Elasticsearch\Client;

/**
 * The main query class to make querying/fetching data from elasticsearch easy. 
 * 
 * It is a general class used for querying one specific index. If you are 
 * looking for a general-index search, then use the official client sdk 
 * libraries directly.
 *
 * @package ItvisionSy\EsMapper
 * @author Muhannad Shelleh <muhannad.shelleh@itvision-sy.com>
 * 
 * @method Result query(string $type, array $parameters=[])
 * @method Result all(string $type)
 * @property-read Client $client
 */
abstract class Query {

    protected $config;
    protected $client;

    /**
     * Factory method for creating query objects
     * 
     * @param array $config The config to use for the client
     * @return Query
     */
    public static function make(array $config = []) {
        return new static($config);
    }

    /**
     * Query object constructor
     * 
     * @param array $config
     */
    public function __construct(array $config = []) {
        $config += $this->defaults();
        $this->config = $config;
        $this->establish($config, true);
    }

    /**
     * The index name to be used.
     * Should return a string ends with namespace separator path or 
     * contains the {type} string as a placeholder for the actual model class name.
     * 
     * i.e. 
     * 
     * return "\\"; 
     * //means the model classes for Foo and Bar will be \Foo and \Bar
     * 
     * return "\\Models\\"; 
     * //means the model classes for Foo and Bar will be \Models\Foo and 
     * //   \Models\Bar
     * 
     * return "\\Models\\{type}Model";
     * //means the model classes for Foo and Bar will be \Models\FooModel and 
     * //   \Models\BarModel
     * 
     * @return string
     */
    abstract public function index();

    /**
     * Gets the namespace/class pattern will be used to create Model objects.
     * Should end with \\ 
     * Default value is \ means the root global namespace.
     * 
     * @return string
     */
    public function modelClassNamePattern() {
        return "\\";
    }

    /**
     * Gets the index name for this query class
     * @return string
     */
    public function getIndex() {
        return $this->index();
    }

    /**
     * Gets the current ES client in use for the object.
     * 
     * @return Client
     */
    public function getClient() {
        return $this->client;
    }

    /**
     * Establishes the ES client using the provided config.
     * Provided config values will take place over the object's config attribute.
     * Finally, the defaults() result will be used as a final config source.
     * 
     * @param array $config Config value to override the general config params
     * @return Client
     */
    public function establish(array $config = [], $assign = false) {
        $client = new Client($config + $this->config + $this->defaults());
        if ($assign) {
            $this->client = $client;
        }
        return $client;
    }

    /**
     * A decorator class to get all index type documents.
     * 
     * @param string $type
     * @return Result|Model[]
     */
    protected function _all($type) {
        return $this->__query($this->index(), $type);
    }

    /**
     * A decorator method to search for ES documents.
     * 
     * @param string $type
     * @param array $query
     * @return Result|Model[]
     */
    protected function _query($type, array $query = []) {
        return $this->__query($this->index(), $type, $query);
    }

    /**
     * A decorator method to get specific document by its id.
     * 
     * @param string $type
     * @param mixed $id
     * @return Model
     */
    protected function _find($type, $id) {
        return $this->__find($this->index(), $type, $id);
    }

    /**
     * The actual method to call client's search method.
     * Returns Result object
     * 
     * @param string $index
     * @param string $type if null, then all types will be searched
     * @param array $query
     * @return Result|Model[]
     */
    protected function __query($index, $type = null, array $query = []) {
        $result = $this->client->search([
            'index' => $index,
            'body' => $query
                ] + ($type ? ['type' => $type] : []));
        return $this->_makeResult($result);
    }

    /**
     * The actual method to call client's get method.
     * Returns either a Model object or null on failure.
     * 
     * @param string $index
     * @param string $type
     * @param sring|int $id
     * @return null|Model
     */
    protected function __find($index, $type, $id) {
        try {
            $result = $this->client->get([
                'index' => $index,
                'type' => $type,
                'id' => $id
            ]);
            if ($result['found']) {
                return $this->_makeModel($result);
            } else {
                return null;
            }
        } catch (\Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            return null;
        }
    }

    /**
     * Creates a results set for ES query hits
     * 
     * @param array $result
     * @return Result
     */
    protected function _makeResult(array $result) {
        return Result::make($result)->setModelClass($this->_fullModelClassNamePattern());
    }

    /**
     * Creates a model object for a specific es result hits entry
     * 
     * @param array $source
     * @return Model
     */
    protected function _makeModel(array $source) {
        return Model::makeOfType($source, $this->_fullModelClassNamePattern());
    }

    /**
     * Returns the full namespace string for the model. 
     * If the provided namespace contains {type} then use it, 
     * otherwize add {type} to the end.
     * 
     * @return string
     */
    protected function _fullModelClassNamePattern() {
        return stripos($this->modelClassNamePattern()? : "", '{type}') !== false ? $this->modelClassNamePattern() : "{$this->modelClassNamePattern()}{type}";
    }

    public static function __callStatic($name, $arguments) {
        switch ($name) {
            case 'query':
            case 'all':
            case 'find':
                return call_user_func_array([static::make(), $name], $arguments);
        }
    }

    public function __call($name, $arguments) {
        switch ($name) {
            case 'query':
            case 'all':
            case 'find':
                return call_user_func_array([$this, "_{$name}"], $arguments);
        }
    }

    /**
     * The default values for the client
     * 
     * @return array
     */
    protected function defaults() {
        return [
            'hosts' => [
                'http://localhost:9200/'
            ]
        ];
    }

}
