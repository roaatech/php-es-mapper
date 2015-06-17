<?php

namespace ItvisionSy\EsMapper;

use Elasticsearch\Client;

/**
 * Description of Query
 *
 * @author muhannad
 * 
 * @method Result query(string $type, array $parameters=[])
 * @method Result all(string $type)
 * @property-read Client $client
 */
abstract class Query {

    protected $config;
    protected $client;

    /**
     * 
     * @return Query
     */
    public static function make(array $config = []) {
        return new static($config);
    }

    public function __construct(array $config = []) {
        $config += ['hosts' => ['http://localhost:9200/']];
        $this->config = $config;
        $this->estalbish();
    }

    abstract public function index();

    public function modelNamespace() {
        return "\\";
    }

    public function getIndex() {
        return $this->index();
    }

    /**
     * 
     * @return Client
     */
    public function getClient() {
        return $this->client;
    }

    /**
     * 
     * @return Query
     */
    public function estalbish() {
        $this->client = new Client($this->config);
        return $this;
    }

    /**
     * 
     * @param type $type
     * @return Result
     */
    protected function _all($type) {
        return $this->__query($this->index(), $type);
    }

    /**
     * 
     * @param type $type
     * @param array $query
     * @return Result
     */
    protected function _query($type, array $query = []) {
        return $this->__query($this->index(), $type, $query);
    }

    /**
     * 
     * @param string $type
     * @param mixed $id
     * @return Model
     */
    protected function _find($type, $id) {
        return $this->__find($this->index(), $type, $id);
    }

    /**
     * 
     * @param type $index
     * @param type $type
     * @param array $query
     * @return Result
     */
    protected function __query($index, $type = null, array $query = []) {
        $result = $this->client->search([
            'index' => $index,
            'body' => $query
                ] + ($type ? ['type' => $type] : []));
        return $this->_makeResult($result);
    }

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
     * 
     * @param array $result
     * @return Result
     */
    protected function _makeResult(array $result) {
        return Result::make($result)->setModelClass($this->_fullModelNamespace());
    }

    protected function _makeModel(array $source) {
        return Model::makeOfType($source, $this->_fullModelNamespace());
    }

    protected function _fullModelNamespace() {
        return stripos($this->modelNamespace()?:"", '{type}') !== false ? $this->modelNamespace() : "{$this->modelNamespace()}{type}";
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

}
