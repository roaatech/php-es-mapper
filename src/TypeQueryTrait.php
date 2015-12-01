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

/**
 * This trait implements extra methods and override parent ones to implement the
 * typed-level query class.
 *
 * @package ItvisionSy\EsMapper
 * @author Muhannad Shelleh <muhannad.shelleh@itvision-sy.com>
 * 
 */
trait TypeQueryTrait {

    /**
     * Overrides the parent class _all method to omit the type in parameters.
     * @see Query::_all for details.
     * 
     * @return Result|Model[]
     */
    protected function _all() {
        return parent::_all($this->type());
    }

    /**
     * Overrides the parent class _query method to omit the type in parameters.
     * @see Query::_query for details.
     * 
     * @param array $query
     * @return Result|Model[]
     */
    protected function _query(array $query = array()) {
        return parent::_query($this->type(), $query);
    }

    /**
     * Overrides the parent class _find method to omit the type in parameters.
     * @see Query::_find for details.
     * 
     * @param mixed $id
     * @return Result|Model[]
     */
    protected function _find($id) {
        return parent::_find($this->type(), $id);
    }

    /**
     * Overrides the parent class _create method to omit the type in parameters.
     * @see Query::_create for details.
     * 
     * @param mixed $id
     * @return Result|Model[]
     */
    protected function _create(array $data, $id = null, array $parameters = []) {
        return parent::_create($data, $this->type(), $id, $parameters);
    }

    /**
     * Overrides the parent class _index method to omit the type in parameters.
     * @see Query::_index for details.
     * 
     * @param mixed $id
     * @return Result|Model[]
     */
    protected function _index(array $data, $id = null, array $parameters = []) {
        return parent::_index($data, $this->type(), $id, $parameters);
    }

    /**
     * Creates the full model class name
     * 
     * It uses the query::modelClassNamePattern and typequery::modelClassName
     * methods to create the full classname.
     * 
     * @return string
     */
    protected function _fullModelClass() {
        $baseClassName = $this->modelClassName();
        $classNamePattern = $this->modelClassNamePattern();
        if ($classNamePattern && strpos($classNamePattern, '{type}') !== false) {
            $fullClassName = str_replace("{type}", $baseClassName, $classNamePattern);
        } elseif ($classNamePattern) {
            $fullClassName = "{$classNamePattern}{$baseClassName}";
        } else {
            $fullClassName = "\\{$baseClassName}";
        }
        return $fullClassName;
    }

    /**
     * Overrides the parent class _makeResult method to pass the correct model
     * class name.
     * @see Query::_makeResult for details.
     * 
     * @param array $result
     * @return Result|Model[]
     */
    protected function _makeResult(array $result) {
        return Result::make($result, $this->getClient())->setModelClass($this->_fullModelClass());
    }

    /**
     * Overrides the parent class _makeModel method to pass the correct model
     * class name.
     * @see Query::_makeModel for details.
     * 
     * @param array $source
     * @return Model
     */
    protected function _makeModel(array $source) {
        return Model::makeOfType($source, $this->_fullModelClass(), $this->getClient());
    }

    /**
     * Overrides the parent class _delete method to pass the correct type
     * @see Query::_delete for details
     * 
     * @param scalar $id
     * @return array The elastic command result
     */
    protected function _delete($id){
        return parent::_delete($this->type(), $id);
    }

}
