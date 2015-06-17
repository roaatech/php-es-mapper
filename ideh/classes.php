<?php

namespace ItvisionSy\EsMapper;

use Elasticsearch\Client;

/**
 * Description of Query
 *
 * @author muhannad
 * 
 */
abstract class Query {

    /**
     * 
     * @return Query
     */
    public static function make(array $config) {
        
    }

    public function __construct(array $config) {
        
    }

    public function index() {
        
    }

    public function modelNamespace() {
        
    }

    public function getIndex() {
        
    }

    /**
     * 
     * @return Client
     */
    public function getClient() {
        
    }

    /**
     * 
     * @return Query
     */
    public function estalbish() {
        
    }

    /**
     * 
     * @param type $type
     * @return Result
     */
    public function all($type) {
        
    }

    /**
     * 
     * @param type $type
     * @return Result
     */
    public static function all($type) {
        
    }

    /**
     * 
     * @param type $type
     * @param array $query
     * @return Result
     */
    public function query($type, array $query = []) {
        
    }

    /**
     * 
     * @param type $type
     * @param array $query
     * @return Result
     */
    public static function query($type, array $query = []) {
        
    }

    /**
     * 
     * @param type $type
     * @param array $id
     * @return Result
     */
    public function find($type, $id) {
        
    }

    /**
     * 
     * @param type $type
     * @param array $id
     * @return Result
     */
    public static function find($type, $id) {
        
    }

}
