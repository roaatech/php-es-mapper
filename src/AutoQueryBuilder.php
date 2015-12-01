<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ItvisionSy\EsMapper;

use BadMethodCallException;

/**
 * Description of AutoQueryBuilder
 *
 * @author muhannad
 */
class AutoQueryBuilder extends QueryBuilder {

    protected $queryInstance;
    
    /**
     * Sets the query instance to be used
     * 
     * @param Query $instance
     * @return AutoQueryBuilder
     */
    public function setQueryInstance(Query $instance){
        $this->queryInstance = $instance;
        return $this;
    }
    
    /**
     * Executes the query against the passed query instance
     * 
     * @return Result
     * @throws BadMethodCallException
     */
    public function execute(){
        if(!$this->queryInstance){
            throw new BadMethodCallException("Query instance is not passed. Consider calling \$object->setQueryInstance(\$istance) first");
        }
        return $this->queryInstance->query($this->toArray());
    }
    
    /**
     * Initiates a new query builder for a specific Query instance
     * 
     * This helps in fluiding the query stream. i.e.
     * SomeTypeQuery::make($config)->builder()->where("id",8)->where("age",">=",19)->execute();
     * 
     * 
     * @param Query $instance
     * @param array $query
     * @return AutoQueryBuilder
     */
    public static function makeForQueryInstance(Query $instance, array $query = []){
        $query = static::make($query);
        return $query->setQueryInstance($instance);
    }

}
