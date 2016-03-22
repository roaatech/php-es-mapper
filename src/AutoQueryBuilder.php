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

use BadMethodCallException;

/**
 * A DSL query builder class with a support of execution against a Query class.
 *
 * @package ItvisionSy\EsMapper
 * @author Muhannad Shelleh <muhannad.shelleh@itvision-sy.com>
 */
class AutoQueryBuilder extends QueryBuilder {

    protected $queryInstance;

    /**
     * Sets the query instance to be used
     * 
     * @param Query $instance
     * @return AutoQueryBuilder
     */
    public function setQueryInstance(Query $instance) {
        $this->queryInstance = $instance;
        return $this;
    }

    /**
     * Executes the query against the passed query instance
     * 
     * @return Result
     * @throws BadMethodCallException
     */
    public function execute() {
        if (!$this->queryInstance) {
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
    public static function makeForQueryInstance(Query $instance, array $query = []) {
        $query = static::make($query);
        return $query->setQueryInstance($instance);
    }

}
