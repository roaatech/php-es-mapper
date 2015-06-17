<?php

namespace ItvisionSy\EsMapper;

/**
 * Description of TypeQuery
 *
 * @author muhannad
 */
trait TypeQueryTrait {

    protected function _all() {
        return parent::_all($this->type());
    }

    protected function _query(array $query = array()) {
        return parent::_query($this->type(), $query);
    }

    protected function _find($id) {
        return parent::_find($this->type(), $id);
    }

    protected function _fullModelClass() {
        return "{$this->modelNamespace()}{$this->modelClass()}";
    }

    protected function _makeResult(array $result) {
        return Result::make($result)->setModelClass($this->_fullModelClass());
    }

    protected function _makeModel(array $source) {
        return Model::makeOfType($source, $this->_fullModelClass());
    }

}
