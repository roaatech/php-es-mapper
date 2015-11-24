<?php

namespace ItvisionSy\EsMapper;

/**
 * Description of QueryBuilder
 *
 * @author muhannad
 */
class QueryBuilder {

    protected $query = [];

    public static function __callStatic($name, $arguments) {
        switch ($name) {
            case 'make':
                return static::__make(array_key_exists(0, $arguments) ? $arguments[0] : []);
        }
    }

    public function __call($name, $arguments) {
        $queryName = "{$name}Query";
        if (method_exists($this, $queryName)) {
            return call_user_func_array([$this, $queryName], $arguments);
        }
    }

    protected static function __make(array $query = []) {
        return new static($query);
    }

    public function __construct(array $query = []) {
        $this->query = array_merge_recursive($query, []);
    }

    public function toArray() {
        return $this->query;
    }

    public function toJSON() {
        return json_encode($this->query);
    }

    public function emptyQuery() {
        $this->query = [];
        return $this;
    }

    public function sort($by, $directions = "asc", $override = false) {
        $this->assertInitiated("sort");
        if ($override) {
            $this->query['sort'] = [];
        }
        $this->query['sort'][] = [$key => $directions];
        return $this;
    }

    public function where($key, $value, $compare = "=", $filter = null, $params = []) {
        $compare = trim($compare);
        $bool = substr($compare, 0, 1) == "!" ? "must_not" : (substr($compare, 0, 1) == "?" ? "should" : "must");
        if ($bool !== "must") {
            $compare = substr($compare, 1);
        }
        $tool = $suffix = $prefix = null;
        switch (strtolower(str_replace("_", " ", $compare))) {
            case '=':
            case 'equals':
            case 'term':
            default:
                $tool = "term";
                break;
            case '>':
            case 'gt':
                $tool = "range";
                $operator = "gt";
                break;
            case '<':
            case 'lt':
                $tool = "range";
                $operator = "lt";
                break;
            case '><':
            case '<>':
            case 'between':
                $tool = "range";
                $operator = ["gt", "lt"];
                break;
            case '>=<=':
            case '<=>=':
            case 'between from to':
                $tool = "range";
                $operator = ["gte", "lte"];
                break;
            case '>=<':
            case '<>=':
            case 'between from':
                $tool = "range";
                $operator = ["gte", "lt"];
                break;
            case '><=':
            case '<=>':
            case 'between to':
                $tool = "range";
                $operator = ["gt", "lte"];
                break;
            case '>=':
            case 'gte':
                $tool = "range";
                $operator = "gte";
                break;
            case '<=':
            case 'lte':
                $tool = "range";
                $operator = "lte";
                break;
            case '*=':
            case 'suffix':
            case 'suffixed':
            case 'ends with':
            case 'ends':
                $tool = "wildcard";
                $suffix = "*";
                $filter = false;
                break;
            case '=*':
            case 'starts':
            case 'starts with':
            case 'prefix':
            case 'prefixed':
                $tool = "prefix";
                break;
            case '*=*':
            case 'like':
            case 'wildcard':
                $tool = "wildcard";
                $filter = false;
                break;
            case '**':
            case 'r':
            case 'regexp':
            case 'rx':
                $tool = "regexp";
                break;
            case '*':
            case 'match':
                $tool = "match";
                $filter = false;
                break;
        }

        if ($filter === null) {
            $filter = true;
        }

        if (($suffix || $prefix) && is_array($value)) {
            foreach ($value as $index => $singleValue) {
                if (is_string($singleValue)) {
                    $value[$index] = $prefix . $singleValue . $suffix;
                }
            }
        }

        switch ($tool) {
            case 'match':
                return $this->match($key, $value, $bool, $params);
            case 'regexp':
                return $this->regexp($key, $value, $bool, $params);
            case 'term':
                return $this->term($key, $value, $bool, $filter, $params);
            case 'range':
                return $this->range($key, $operator, $value, $bool, $filter, $params);
            case 'wildcard':
                return $this->wildcard($key, $value, $bool, $params);
            case 'prefix':
                return $this->prefix($key, $value, $bool, $params);
        }
    }

    public function wildcard($key, $value, $bool = "must", array $params = []) {
        if (is_array($key)) {
            return $this->multiWildcard($key, $value, $bool, $params);
        }
        if (is_array($value)) {
            return $this->wildcards($key, $value, $bool, $params);
        }
        $this->addBool(["wildcard" => [$key => ["wildcard" => $value]]], $bool, false, $params);
        return $this;
    }

    public function wildcards($key, array $values, $bool = "must", array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($values as $value) {
            $subQuery->wildcard($key, $value, $bool, $params);
        }
        $subQuery->endSubQuery();
        return $this;
    }

    public function multiWildcard(array $keys, $value, $bool = "must", array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($keys as $key) {
            $subQuery->wildcard($key, $value, $bool, $params);
        }
        $subQuery->endSubQuery();
        return $this;
    }

    public function multiWildcards(array $keys, array $values, $bool = "must", array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($keys as $key) {
            $subQuery->wildcards($key, $values, $bool, $params);
        }
        $subQuery->endSubQuery();
        return $this;
    }

    public function regexp($key, $value, $bool = "must", array $params = []) {
        if (is_array($key)) {
            return $this->multiRegexp($key, $value, $bool, $params);
        }
        if (is_array($value)) {
            return $this->regexps($key, $value, $bool, $params);
        }
        $this->addBool(["regexp" => [$key => ["value" => $value]]], $bool, false, $params);
        return $this;
    }

    public function regexps($key, array $values, $bool = "must", array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($values as $value) {
            $subQuery->regexp($key, $value, $bool, $params);
        }
        $subQuery->endSubQuery();
        return $this;
    }

    public function multiRegexp(array $keys, $value, $bool = "must", array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($keys as $key) {
            $subQuery->regexp($key, $value, $bool, $params);
        }
        $subQuery->endSubQuery();
        return $this;
    }

    public function multiRegexps(array $keys, array $values, $bool = "must", array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($keys as $key) {
            $subQuery->regexps($key, $values, $bool, $params);
        }
        $subQuery->endSubQuery();
        return $this;
    }

    public function prefix($key, $value, $bool = "must", array $params = []) {
        if (is_array($key)) {
            return $this->multiPrefix($key, $value, $bool, $params);
        }
        if (is_array($value)) {
            return $this->prefixs($key, $value, $bool, $params);
        }
        $this->addBool(["prefix" => [$key => $value]], $bool, false, $params);
        return $this;
    }

    public function prefixs($key, array $values, $bool = "must", array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($values as $value) {
            $subQuery->prefix($key, $value, $bool, $params);
        }
        $subQuery->endSubQuery();
        return $this;
    }

    /**
     * Creates multiple prefix queries for the list of keys
     * 
     * Creates a prefix query for the $value for each key in the $keys
     * 
     * @param array $keys
     * @param type $value
     * @param type $bool
     * @param array $params
     * @return \ItvisionSy\EsMapper\QueryBuilder
     */
    public function multiPrefix(array $keys, $value, $bool = "must", array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($keys as $key) {
            $subQuery->prefix($key, $value, $bool, $params);
        }
        $subQuery->endSubQuery();
        return $this;
    }

    /**
     * Creates multiple prefixes queries for the list of keys
     * 
     * Two keys and two values will results in 4 prefix queries
     * 
     * @param array $keys
     * @param array $values
     * @param type $bool
     * @param array $params
     * @return \ItvisionSy\EsMapper\QueryBuilder
     */
    public function multiPrefixs(array $keys, array $values, $bool = "must", array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($keys as $key) {
            $subQuery->prefixs($key, $values, $bool, $params);
        }
        $subQuery->endSubQuery();
        return $this;
    }

    /**
     * A normal term query
     * 
     * @param type $key
     * @param type $value
     * @param type $bool
     * @param type $filter
     * @param array $params
     * @return \ItvisionSy\EsMapper\QueryBuilder
     */
    public function term($key, $value, $bool = "must", $filter = false, array $params = []) {
        if (is_array($key)) {
            return $this->multiTerm($key, $value, $bool, $filter, $params);
        }
        $tool = "term" . (is_array($value) ? "s" : "");
        $this->addBool([$tool => [$key => ["value" => $value]]], $bool, $filter, $params);
        return $this;
    }

    /**
     * Multiple OR joined term queries
     * 
     * @param array $keys
     * @param type $value
     * @param type $bool
     * @param type $filter
     * @param array $params
     * @return \ItvisionSy\EsMapper\QueryBuilder
     */
    public function multiTerm(array $keys, $value, $bool = "must", $filter = false, array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($keys as $key) {
            $subQuery->term($key, $value, $bool, $filter, $params);
        }
        $subQuery->endSubQuery();
        return $this;
    }

    /**
     * A match query
     * 
     * @param type $key
     * @param type $value
     * @param type $bool
     * @param array $params
     * @return \ItvisionSy\EsMapper\QueryBuilder
     */
    public function match($key, $value, $bool, array $params = []) {
        if (is_array($key)) {
            return $this->multiMatch($key, $value, $bool, $params);
        }
        if (is_array($value)) {
            return $this->matches($key, $value, $bool, $params);
        }
        $this->addBool(["match" => [$key => ["query" => $value]]], $bool, false, $params);
        return $this;
    }

    /**
     * Creates multiple match queries for each value in the array
     * 
     * Queries will be joined by an OR filter
     * 
     * @param string $key the key to create the matches for
     * @param array $values a list of values to create a match query for each
     * @param type $bool
     * @param array $params
     * @return \ItvisionSy\EsMapper\QueryBuilder
     */
    public function matches($key, array $values, $bool, array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($values as $value) {
            $subQuery->match($key, $value, $bool, $params);
        }
        $subQuery->endSubQuery();
        return $this;
    }

    /**
     * Creates a mutli_match query
     * 
     * @param array $keys keys(fields) of the multimatch
     * @param type $value
     * @param type $bool
     * @param array $params
     * @return \ItvisionSy\EsMapper\QueryBuilder
     */
    public function multiMatch(array $keys, $value, $bool, array $params = []) {
        $this->addBool(["multi_match" => ["query" => $value, "fields" => $keys]], $bool, false, $params);
        return $this;
    }

    /**
     * Creates multiple mutli_match queries for each value in the array
     * 
     * Queries will be joined by an OR filter
     * 
     * @param array $keys keys(fields) of the multimatch
     * @param scalar[] $values a list of values to create a multimatch query for
     * @param type $bool
     * @param array $params
     * @return \ItvisionSy\EsMapper\QueryBuilder
     */
    public function multiMatches(array $keys, array $values, $bool, array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($values as $value) {
            $subQuery->multiMatch($keys, $value, $bool, $params);
        }
        $subQuery->endSubQuery();
        return $this;
    }

    /**
     * Creates a range query
     * 
     * @param string $key The key to create the range query for
     * @param string|string[] $operator lt, gt, lte, gte. Can be an array of mixed lt,gt,lte,gte; and if so, it should match the number of elements in the $value array too.
     * @param scalar|scalar[] $value the value for the range comparison. Should be an array of same element count if the $operator is also an array.
     * @param string $bool must, should, or must_not
     * @param boolean $filter to use the filter part instead of the query part
     * @param array $params additional query parameters for the range query
     * @return \ItvisionSy\EsMapper\QueryBuilder
     * @throws \BadMethodCallException
     */
    public function range($key, $operator, $value, $bool, $filter, array $params = []) {
        if (is_array($operator) && !is_array($value) || !is_array($operator) && is_array($value) || is_array($operator) && count($operator) !== count($value)) {
            throw new \BadMethodCallException();
        }
        if (is_array($key)) {
            return $this->multiRange($key, $operator, $value, $bool, $filter, $params);
        }
        $query = [];
        $operators = (array) $operator;
        $values = (array) $value;
        foreach ($operators as $index => $value) {
            $query[$operator] = $values[$index];
        }
        $this->addBool(["range" => [$key => $query]], $bool, $filter, $params);
        return $this;
    }

    /**
     * Creates multiple range queries joined by OR
     * 
     * For each key in the keys, a new range query will be created
     * 
     * @param array $keys keys to create a range query for each of them
     * @param string|string[] $operator lt, gt, lte, gte. Can be an array of mixed lt,gt,lte,gte; and if so, it should match the number of elements in the $value array too.
     * @param scalar|scalar[] $value the value for the range comparison. Should be an array of same element count if the $operator is also an array.
     * @param string $bool must, should, or must_not
     * @param boolean $filter to use the filter part instead of the query part
     * @param array $params additional query parameters for the range query
     * @return \ItvisionSy\EsMapper\QueryBuilder
     */
    public function multiRange(array $keys, $operator, $value, $bool, $filter, array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($keys as $key) {
            $subQuery->range($key, $operator, $value, $bool, $filter, $params);
        }
        $subQuery->endSubQuery();
        return $this;
    }

    /**
     * Creates an AND filter subquery
     * 
     * @param string $bool must, should, or must_not
     * @param array $params extra params for and filter
     * @return SubQueryBuilder
     */
    public function andWhere($bool = "must", array $params = []) {
        $callback = function(SubQueryBuilder $subQuery) use ($bool, $params) {
            return $this->_endChildSubQuery("and", $subQuery->toArray(), $bool, $params);
        };
        return SubQueryBuilder::make($callback);
    }

    /**
     * Creates an OR filter subquery
     * 
     * @param string $bool must, should, or must_not
     * @param array $params extra params for or filter
     * @return SubQueryBuilder
     */
    public function orWhere($bool = "must", array $params = []) {
        $callback = function(SubQueryBuilder $subQuery) use ($bool, $params) {
            return $this->_endChildSubQuery("or", $subQuery->toArray(), $bool, $params);
        };
        return SubQueryBuilder::make($callback);
    }

    /**
     * Receives the end signal from the sub query object
     * 
     * @param string $tool [and|or]
     * @param array $subQuery
     * @param type $bool
     * @param array $params
     * @return \ItvisionSy\EsMapper\QueryBuilder
     */
    protected function _endChildSubQuery($tool, array $subQuery, $bool, array $params = []) {
        $this->addBool([$tool => $subQuery], $bool, true, $params);
        return $this;
    }

    /**
     * Checks and creates the required structure
     * 
     * @param string $key A dot [.] separated path to be created/checked
     * @return void
     */
    protected function assertInitiated($key) {
        $current = &$this->query;
        $keys = explode(".", $key);
        foreach ($keys as $element) {
            if (!array_key_exists($element, $current)) {
                $current[$element] = [];
            }
            $current = &$current[$element];
        }
    }

    /**
     * Adds a raw must filter part
     * 
     * @param array $query
     * @return \ItvisionSy\EsMapper\QueryBuilder
     */
    public function rawMustFilter(array $query) {
        $this->addBool($query, "must", true);
        return $this;
    }

    /**
     * Adds a raw must not filter part
     * 
     * @param array $query
     * @return \ItvisionSy\EsMapper\QueryBuilder
     */
    public function rawMustNotFilter(array $query) {
        $this->addBool($query, "must_not", true);
        return $this;
    }

    /**
     * Adds a raw should filter part
     * 
     * @param array $query
     * @return \ItvisionSy\EsMapper\QueryBuilder
     */
    public function rawShouldFilter(array $query) {
        $this->addBool($query, "should", true);
        return $this;
    }

    /**
     * Adds a raw must query part
     * @param array $query
     * @return \ItvisionSy\EsMapper\QueryBuilder
     */
    public function rawMustQuery(array $query) {
        $this->addBool($query, "must", false);
        return $this;
    }

    /**
     * Adds a raw must_not query part
     * 
     * @param array $query
     * @return \ItvisionSy\EsMapper\QueryBuilder
     */
    public function rawMustNotQuery(array $query) {
        $this->addBool($query, "must_not", false);
        return $this;
    }

    /**
     * Adds a raw bool should query part
     * 
     * @param array $query
     * @return \ItvisionSy\EsMapper\QueryBuilder
     */
    public function rawShouldQuery(array $query) {
        $this->addBool($query, "should", false);
        return $this;
    }

    /**
     * Adds a new bool query part to the query array
     * 
     * @param array $query the bool query part to be added
     * @param string $bool the bool group (must, should, must_not)
     * @param boolean $filter weither to be added to the filter or the query party
     * @param array $params extra parameters for the query part (will be merged into the original part)
     * 
     * @return void
     */
    protected function addBool(array $query, $bool, $filter = false, array $params = []) {
        $filtered = $filter ? "filter" : "query";
        $key = "query.filtered.{$filtered}.bool.{$bool}";
        if ($filter) {
            $this->assertInitiated($key);
        }
        $this->query["query"]["filtered"][$filtered]["bool"][$bool][] = array_merge_recursive($query, $params);
    }

}
