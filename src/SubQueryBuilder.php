<?php

namespace ItvisionSy\EsMapper;

/**
 * Description of SubQueryBuilder
 *
 * @author muhannad
 */
class SubQueryBuilder extends QueryBuilder {

    protected $endCallback;

    protected static function __makeSub(callable $endCallback) {
        return new static($endCallback);
    }

    public static function __callStatic($name, $arguments) {
        switch ($name) {
            case 'make':
                return static::__makeSub(array_key_exists(0, $arguments) ? $arguments[0] : null);
        }
    }

    public function __construct(callable $endCallback) {
        $this->endCallback = $endCallback;
    }

    protected function addBool(array $query, $bool, $filter = false, array $params = []) {
        $this->query[] = array_merge_recursive($query, $params);
    }

    public function endSubQuery() {
        $callback = $this->endCallback;

        return $callback($this);
    }

}
