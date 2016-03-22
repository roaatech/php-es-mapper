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
 * An Elasticsearch DSL query builder for nesting queries inside each other.
 * 
 * Basically, you will not need to inistantiate it directly. It will be auto-
 * instantiated and closed from another query builder when it is needed.
 *
 * @package ItvisionSy\EsMapper
 * @author Muhannad Shelleh <muhannad.shelleh@itvision-sy.com>
 * 
 * @method-static self|static|SubQueryBuilder call(callable $endCallback)
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

    /**
     * 
     * @param callable $endCallback The callback function to call once the sub
     *              query builder finishes its work.
     */
    public function __construct(callable $endCallback) {
        $this->endCallback = $endCallback;
    }

    protected function addBool(array $query, $bool, $filter = false, array $params = []) {
        $this->query[] = array_merge_recursive($query, $params);
    }

    /**
     * Finishes the sub query builder context and returns to the original query 
     * builder context.
     * 
     * It will call the provided callable $endCallback function when inistantia-
     * ted.
     * 
     * @return QueryBuilder
     */
    public function endSubQuery() {
        $callback = $this->endCallback;

        return $callback($this);
    }

}
