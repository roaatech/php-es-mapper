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
 * Elasticsearch DSL query builder
 * 
 * The elastic query builder class is an easy way to create complex elastic DSL
 * queries. It has a fluid interface, as well a smart detection feature to make
 * building a query an easy task for developers not familier with elastic DSL
 * terms and rules.
 * 
 * Example: 
 * QueryBuilder::make([$initialQuery]) //initiate a builder with base query
 *   ->where('name','Elastic')          //term:{name:'Elastic'}
 *   ->where('version', ['1.6','2.0'])  //terms:{version:['1.6','2.0']}
 *   ->where(['sdk','client'],'PHP')    //or:[{term:{sdk:'PHP'}},{term:{...}}]
 *   ->where(['created','updated'],['yesterday','today']) //or:[{terms},{terms}]
 *   ->toArray();                       //get the final query array
 * 
 * It worths to know that the query builder depends on the bool query/filter:
 *  [
 *      'query'=>[
 *          'filtered'=>[
 *              'filter'=>[
 *                  'bool'=>[
 *                      'must'=>[...],
 *                      'should'=>[...],
 *                      'must_not'=>[...],
 *                  ]
 *              ],
 *              'query'=>[
 *                  'bool'=>[
 *                      'must'=>[...],
 *                      'should'=>[...],
 *                      'must_not'=>[...],
 *                  ]
 *              ]
 *          ]
 *      ]
 *  ]
 *
 * @package ItvisionSy\EsMapper
 * @author Muhannad Shelleh <muhannad.shelleh@itvision-sy.com>
 * 
 * @method static|self|QueryBuilder make(array $baseQuery=[])
 * @method-static static|self|QueryBuilder make(array $baseQuery=[])
 * 
 * @see AutoQueryBuilder
 * 
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

    /**
     * 
     * @param array $query The base query to start building on it
     */
    public function __construct(array $query = []) {
        $this->query = array_merge_recursive($query, []);
    }

    /**
     * Returns the final DSL query as a PHP assoc array
     * @return array
     */
    public function toArray() {
        return $this->query;
    }

    /**
     * Returns the final DSL query as a JSON string
     * @return type
     */
    public function toJSON() {
        return json_encode($this->query);
    }

    /**
     * Resets the query to an empty query.
     * 
     * @return QueryBuilder|static|self
     */
    public function emptyQuery(array $baseQuery = []) {
        $this->query = $baseQuery;
        return $this;
    }

    /**
     * Adds a sort clause to the query
     * 
     * @param string $by the key to sort by it
     * @param string|array $direction the direction of the sort. 
     *                      Can be an array for extra sort control.
     * @param boolean $override false by default. if true it will reset the sort
     *                      first, then add the new sort entry.
     * @return QueryBuilder|static|self
     */
    public function sort($by, $direction = "asc", $override = false) {
        $this->assertInitiated("sort");
        if ($override) {
            $this->query['sort'] = [];
        }
        $this->query['sort'][] = [$by => $direction];
        return $this;
    }

    /**
     * The basic smart method to build queries.
     * 
     * It will automatically identify the correct filter/query tool to use, as 
     * well the correct query part to be used in, depending on the content and
     * type of the key, value, and comparison parameters.
     * 
     * $comparison parameter will define what filter/query tool to use.
     * 
     * @param string|string[] $key the key(s) to check
     * @param string|string[] $value the value(s) to check against
     * @param string $compare the comparison operator or elastic query/filter tool
     *                        prefix it with ! to negate or with ? to convert to should
     * @param boolean $filter false to add as a query, true as a filter
     * @param array|map $params additional parameters the query/filter can use
     * @return static|QueryBuilder
     */
    public function where($key, $value, $compare = "=", $filter = null, $params = []) {

        //identify the bool part must|should|must_not
        $compare = trim($compare);
        $bool = substr($compare, 0, 1) == "!" ? "must_not" : (substr($compare, 0, 1) == "?" ? "should" : "must");

        //get the correct compare value
        if ($bool !== "must") {
            $compare = substr($compare, 1);
        }

        //$suffix and $prefix will be used in regex, wildcard, prefix, and suffix
        //queries.
        $tool = $suffix = $prefix = null;
        //$_filter is the real identifier for the filter
        $_filter = $filter;

        //identify the tool, operator, and part
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
                $prefix = "*";
                $_filter = false;
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
                $prefix = '*';
                $suffix = '*';
                $_filter = false;
                break;
            case '**':
            case 'r':
            case 'regexp':
            case 'regex':
            case 'rx':
                $tool = "regexp";
                break;
            case '*':
            case 'match':
                $tool = "match";
                $_filter = false;
                break;
        }

        //add prefix/suffix to each element in array values
        if ($suffix || $prefix) {
            if (is_array($value)) {
                foreach ($value as $index => $singleValue) {
                    if (is_string($singleValue)) {
                        $value[$index] = $prefix . $singleValue . $suffix;
                    }
                }
            } else {
                $value = ($prefix? : "") . $value . ($suffix? : "");
            }
        }

        //call the real query builder method
        switch ($tool) {
            case 'match':
                return $this->match($key, $value, $bool, $this->shouldBeFilter($filter, $_filter), $params);
            case 'regexp':
                return $this->regexp($key, $value, $bool, $this->shouldBeFilter($filter, $_filter), $params);
            case 'term':
                return $this->term($key, $value, $bool, $this->shouldBeFilter($filter, $_filter), $params);
            case 'range':
                return $this->range($key, $operator, $value, $bool, $this->shouldBeFilter($filter, $_filter), $params);
            case 'wildcard':
                return $this->wildcard($key, $value, $bool, $this->shouldBeFilter($filter, $_filter), $params);
            case 'prefix':
                return $this->prefix($key, $value, $bool, $this->shouldBeFilter($filter, $_filter), $params);
        }
    }

    /**
     * Creates a wildcard query part.
     * 
     * It will automatically detect the $key and $value types to create the 
     * required number of clauses, as follows:
     * 
     * $key         $value      result
     * 
     * single       single      {wildcard:{$key:{wildcard:$value}}}
     * single       array       or:[wildcard($key, $value[1]), ...]
     * array        single      or:[wildcard($key[1], $value), ...]
     * array        array       or:[wildcard($key[1], $value[1]),
     *                              wildcard($key[1], $value[2]), ...
     *                              wildcard($key[2], $value[1]), ... ]
     * 
     * If $filter is true, it will enclose the wildcard query with `query` 
     * filter and add it to the DSL query filter section instead the query 
     * section.
     * 
     * @param string|string[] $key the key(s) to wildcard search in.
     * @param string|string[] $value the value(s) to wildcard search against.
     * @param string $bool severity of the query/filter. [must]|must_not|should
     * @param bool $filter if true, DSL query filter section will be used after
     *                      enclosing in a `query` filter.
     * @param array $params extra parameters for the query tool
     * @return QueryBuilder|static|self
     */
    public function wildcard($key, $value, $bool = "must", $filter = false, array $params = []) {
        if (is_array($key)) {
            return $this->multiWildcard($key, $value, $bool, $filter, $params);
        }
        if (is_array($value)) {
            return $this->wildcards($key, $value, $bool, $filter, $params);
        }
        $this->addBool($this->makeFilteredQuery(["wildcard" => [$key => ["wildcard" => $value]]], $filter), $bool, $filter, $params);
        return $this;
    }

    /**
     * Creates wildcard query for each $value grouped by OR clause
     * 
     * @see QueryBuilder::wildcard() for more information
     * 
     * @param string|string[] $key the key(s) to wildcard search in.
     * @param string[] $values the value(s) to wildcard search against.
     * @param string $bool severity of the query/filter. [must]|must_not|should
     * @param bool $filter if true, DSL query filter section will be used after
     *                      enclosing in a `query` filter.
     * @param array $params extra parameters for the query tool
     * @return QueryBuilder|static|self
     */
    public function wildcards($key, array $values, $bool = "must", $filter = false, array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($values as $value) {
            $subQuery->wildcard($key, $value, $bool, true, $params);
        }
        $subQuery->endSubQuery();
        return $this;
    }

    /**
     * Creates wildcard query for each $key grouped by OR clause
     * 
     * @see QueryBuilder::wildcard() for more information
     * 
     * @param string[] $keys the key(s) to wildcard search in.
     * @param string|string[] $value the value(s) to wildcard search against.
     * @param string $bool severity of the query/filter. [must]|must_not|should
     * @param bool $filter if true, DSL query filter section will be used after
     *                      enclosing in a `query` filter.
     * @param array $params extra parameters for the query tool
     * @return QueryBuilder|static|self
     */
    public function multiWildcard(array $keys, $value, $bool = "must", $filter = false, array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($keys as $key) {
            $subQuery->wildcard($key, $value, $bool, true, $params);
        }
        $subQuery->endSubQuery();
        return $this;
    }

    /**
     * Creates wildcard query for each $key/$value pair grouped by OR clause
     * 
     * @see QueryBuilder::wildcard() for more information
     * 
     * @param string[] $keys the key(s) to wildcard search in.
     * @param string[] $values the value(s) to wildcard search against.
     * @param string $bool severity of the query/filter. [must]|must_not|should
     * @param bool $filter if true, DSL query filter section will be used after
     *                      enclosing in a `query` filter.
     * @param array $params extra parameters for the query tool
     * @return QueryBuilder|static|self
     */
    public function multiWildcards(array $keys, array $values, $bool = "must", $filter = false, array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($keys as $key) {
            $subQuery->wildcards($key, $values, $bool, true, $params);
        }
        $subQuery->endSubQuery();
        return $this;
    }

    /**
     * Creates a regexp query part.
     * 
     * It will automatically detect the $key and $value types to create the 
     * required number of clauses, as follows:
     * 
     * $key         $value      result
     * 
     * single       single      {regexp:{$key:{regexp:$value}}}
     * single       array       or:[regexp($key, $value[1]), ...]
     * array        single      or:[regexp($key[1], $value), ...]
     * array        array       or:[regexp($key[1], $value[1]),
     *                              regexp($key[1], $value[2]), ...
     *                              regexp($key[2], $value[1]), ... ]
     * 
     * If $filter is true, it will enclose the regexp query with `query` 
     * filter and add it to the DSL query filter section instead the query 
     * section.
     * 
     * @param string|string[] $key the key(s) to regexp search in.
     * @param string|string[] $value the value(s) to regexp search against.
     * @param string $bool severity of the query/filter. [must]|must_not|should
     * @param bool $filter if true, DSL query filter section will be used after
     *                      enclosing in a `query` filter.
     * @param array $params extra parameters for the query tool
     * @return QueryBuilder|static|self
     */
    public function regexp($key, $value, $bool = "must", $filter = false, array $params = []) {
        if (is_array($key)) {
            return $this->multiRegexp($key, $value, $bool, $filter, $params);
        }
        if (is_array($value)) {
            return $this->regexps($key, $value, $bool, $filter, $params);
        }
        $this->addBool($this->makeFilteredQuery(["regexp" => [$key => ["value" => $value]]], $filter), $bool, $filter, $params);
        return $this;
    }

    /**
     * Creates regexp query for each $value grouped by OR clause
     * 
     * @see QueryBuilder::regexp() for more information
     * 
     * @param string|string[] $key the key(s) to regexp search in.
     * @param string[] $values the value(s) to regexp search against.
     * @param string $bool severity of the query/filter. [must]|must_not|should
     * @param bool $filter if true, DSL query filter section will be used after
     *                      enclosing in a `query` filter.
     * @param array $params extra parameters for the query tool
     * @return QueryBuilder|static|self
     */
    public function regexps($key, array $values, $bool = "must", $filter = false, array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($values as $value) {
            $subQuery->regexp($key, $value, $bool, true, $params);
        }
        $subQuery->endSubQuery();
        return $this;
    }

    /**
     * Creates regexp query for each $key grouped by OR clause
     * 
     * @see QueryBuilder::regexp() for more information
     * 
     * @param string[] $keys the key(s) to regexp search in.
     * @param string|string[] $value the value(s) to regexp search against.
     * @param string $bool severity of the query/filter. [must]|must_not|should
     * @param bool $filter if true, DSL query filter section will be used after
     *                      enclosing in a `query` filter.
     * @param array $params extra parameters for the query tool
     * @return QueryBuilder|static|self
     */
    public function multiRegexp(array $keys, $value, $bool = "must", $filter = false, array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($keys as $key) {
            $subQuery->regexp($key, $value, $bool, true, $params);
        }
        $subQuery->endSubQuery();
        return $this;
    }

    /**
     * Creates regexp query for each $key/$value pair grouped by OR clause
     * 
     * @see QueryBuilder::regexp() for more information
     * 
     * @param string[] $keys the key(s) to regexp search in.
     * @param string[] $values the value(s) to regexp search against.
     * @param string $bool severity of the query/filter. [must]|must_not|should
     * @param bool $filter if true, DSL query filter section will be used after
     *                      enclosing in a `query` filter.
     * @param array $params extra parameters for the query tool
     * @return QueryBuilder|static|self
     */
    public function multiRegexps(array $keys, array $values, $bool = "must", $filter = false, array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($keys as $key) {
            $subQuery->regexps($key, $values, $bool, true, $params);
        }
        $subQuery->endSubQuery();
        return $this;
    }

    /**
     * Creates a prefix query part.
     * 
     * It will automatically detect the $key and $value types to create the 
     * required number of clauses, as follows:
     * 
     * $key         $value      result
     * 
     * single       single      {prefix:{$key:{prefix:$value}}}
     * single       array       or:[prefix($key, $value[1]), ...]
     * array        single      or:[prefix($key[1], $value), ...]
     * array        array       or:[prefix($key[1], $value[1]),
     *                              prefix($key[1], $value[2]), ...
     *                              prefix($key[2], $value[1]), ... ]
     * 
     * If $filter is true, it will enclose the prefix query with `query` 
     * filter and add it to the DSL query filter section instead the query 
     * section.
     * 
     * @param string|string[] $key the key(s) to prefix search in.
     * @param string|string[] $value the value(s) to prefix search against.
     * @param string $bool severity of the query/filter. [must]|must_not|should
     * @param bool $filter if true, DSL query filter section will be used after
     *                      enclosing in a `query` filter.
     * @param array $params extra parameters for the query tool
     * @return QueryBuilder|static|self
     */
    public function prefix($key, $value, $bool = "must", $filter = false, array $params = []) {
        if (is_array($key)) {
            return $this->multiPrefix($key, $value, $bool, $filter, $params);
        }
        if (is_array($value)) {
            return $this->prefixs($key, $value, $bool, $filter, $params);
        }
        $this->addBool($this->makeFilteredQuery(["prefix" => [$key => $value]], $filter), $bool, $filter, $params);
        return $this;
    }

    /**
     * Creates prefix query for each $value grouped by OR clause
     * 
     * @see QueryBuilder::prefix() for more information
     * 
     * @param string|string[] $key the key(s) to prefix search in.
     * @param string[] $values the value(s) to prefix search against.
     * @param string $bool severity of the query/filter. [must]|must_not|should
     * @param bool $filter if true, DSL query filter section will be used after
     *                      enclosing in a `query` filter.
     * @param array $params extra parameters for the query tool
     * @return QueryBuilder|static|self
     */
    public function prefixs($key, array $values, $bool = "must", $filter = false, array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($values as $value) {
            $subQuery->prefix($key, $value, $bool, true, $params);
        }
        $subQuery->endSubQuery();
        return $this;
    }

    /**
     * Creates OR-joined multiple prefix queries for the list of keys
     * 
     * Creates a prefix query for the $value for each key in the $keys
     * 
     * @param array $keys
     * @param type $value
     * @param type $bool
     * @param array $params
     * @return QueryBuilder|static|self
     */
    public function multiPrefix(array $keys, $value, $bool = "must", $filter = false, array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($keys as $key) {
            $subQuery->prefix($key, $value, $bool, true, $params);
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
     * @return QueryBuilder|static|self
     */
    public function multiPrefixs(array $keys, array $values, $bool = "must", $filter = false, array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($keys as $key) {
            $subQuery->prefixs($key, $values, $bool, true, $params);
        }
        $subQuery->endSubQuery();
        return $this;
    }

    /**
     * A normal term/terms query
     * 
     * It will automatically use term or terms query depending on the type of 
     * $value parameter whether it is an array or not.
     * 
     * @param type $key
     * @param type $value
     * @param type $bool
     * @param type $filter
     * @param array $params
     * @return QueryBuilder|static|self
     */
    public function term($key, $value, $bool = "must", $filter = false, array $params = []) {
        if (is_array($key)) {
            return $this->multiTerm($key, $value, $bool, $filter, $params);
        }
        $tool = "term" . (is_array($value) ? "s" : "");
        $this->addBool([$tool => [$key => (is_array($value) ? $value : ["value" => $value])]], $bool, $filter, $params);
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
     * @return QueryBuilder|static|self
     */
    public function multiTerm(array $keys, $value, $bool = "must", $filter = false, array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($keys as $key) {
            $subQuery->term($key, $value, $bool, true, $params);
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
     * @param boolean $filter
     * @param array $params
     * @return QueryBuilder|static|self
     */
    public function match($key, $value, $bool, $filter = false, array $params = []) {
        if (is_array($key)) {
            return $this->multiMatch($key, $value, $bool, $filter, $params);
        }
        if (is_array($value)) {
            return $this->matches($key, $value, $bool, $filter, $params);
        }
        $this->addBool($this->makeFilteredQuery(["match" => [$key => ["query" => $value] + $params]], $filter), $bool, $filter);
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
     * @return QueryBuilder|static|self
     */
    public function matches($key, array $values, $bool, $filter = false, array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($values as $value) {
            $subQuery->match($key, $value, $bool, true, $params);
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
     * @return QueryBuilder|static|self
     */
    public function multiMatch(array $keys, $value, $bool, $filter = false, array $params = []) {
        $this->addBool($this->makeFilteredQuery(["multi_match" => ["query" => $value, "fields" => $keys] + $params], $filter), $bool, $filter);
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
     * @return QueryBuilder|static|self
     */
    public function multiMatches(array $keys, array $values, $bool, $filter = false, array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($values as $value) {
            $subQuery->multiMatch($keys, $value, $bool, true, $params);
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
     * @return QueryBuilder|static|self
     * @throws \BadMethodCallException
     */
    public function range($key, $operator, $value, $bool, $filter, array $params = []) {
        if (is_array($operator) && !is_array($value) || !is_array($operator) && is_array($value) || is_array($operator) && count($operator) !== count($value)) {
            throw new \BadMethodCallException("Operator and value parameters should be both a scalar type or both an array with same number of elements");
        }
        if (is_array($key)) {
            return $this->multiRange($key, $operator, $value, $bool, $filter, $params);
        }
        $query = [];
        $operators = (array) $operator;
        $values = (array) $value;
        foreach ($operators as $index => $operator) {
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
     * @return QueryBuilder|static|self
     */
    public function multiRange(array $keys, $operator, $value, $bool, $filter, array $params = []) {
        $subQuery = $this->orWhere($bool);
        foreach ($keys as $key) {
            $subQuery->range($key, $operator, $value, $bool, true, $params);
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
     * @return QueryBuilder|static|self
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
     * @return QueryBuilder|static|self
     */
    public function rawMustFilter(array $query) {
        $this->addBool($query, "must", true);
        return $this;
    }

    /**
     * Adds a raw must not filter part
     * 
     * @param array $query
     * @return QueryBuilder|static|self
     */
    public function rawMustNotFilter(array $query) {
        $this->addBool($query, "must_not", true);
        return $this;
    }

    /**
     * Adds a raw should filter part
     * 
     * @param array $query
     * @return QueryBuilder|static|self
     */
    public function rawShouldFilter(array $query) {
        $this->addBool($query, "should", true);
        return $this;
    }

    /**
     * Adds a raw must query part
     * @param array $query
     * @return QueryBuilder|static|self
     */
    public function rawMustQuery(array $query) {
        $this->addBool($query, "must", false);
        return $this;
    }

    /**
     * Adds a raw must_not query part
     * 
     * @param array $query
     * @return QueryBuilder|static|self
     */
    public function rawMustNotQuery(array $query) {
        $this->addBool($query, "must_not", false);
        return $this;
    }

    /**
     * Adds a raw bool should query part
     * 
     * @param array $query
     * @return QueryBuilder|static|self
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

    /**
     * Set the from and size (paging) of the results
     * 
     * @param integer $size
     * @param integer $from
     * @return QueryBuilder|static|self
     */
    public function page($size, $from = null) {
        if ($from) {
            $this->from($from);
        }
        if ($size) {
            $this->size($size);
        }
        return $this;
    }

    /**
     * Sets the size of the results
     * 
     * @param integer $size
     * @return QueryBuilder|static|self
     */
    public function size($size) {
        $this->assertInitiated("size");
        $this->query['size'] = $size;
        return $this;
    }

    /**
     * Sets the start index of the results
     * 
     * @param integer $from
     * @return QueryBuilder|static|self
     */
    public function from($from) {
        $this->assertInitiated("from");
        $this->query['from'] = $from;
        return $this;
    }

    /**
     * Adds a row query part[s] to the current query.
     * 
     * Mainly, it is merging recursivly the $query with the current query
     * 
     * @param array $query
     * @return QueryBuilder|static|self
     */
    public function raw(array $query) {
        array_merge_recursive($this->query, $query);
        return $this;
    }

    protected function makeFilteredQuery(array $queryQuery, $filter = false) {
        return $filter ? ["query" => $queryQuery] : $queryQuery;
    }

    protected function shouldBeFilter($explicit, $implicit) {
        return $implicit === true ? true : ($explicit === false ? false : ($explicit || $implicit ? true : $explicit));
    }

}
