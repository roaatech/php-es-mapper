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

use Elasticsearch\Client;

/**
 * Description of Query
 *
 * @package ItvisionSy\EsMapper
 * @author Muhannad Shelleh <muhannad.shelleh@itvision-sy.com>
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

    /**
     * Retreives the meta data of an index
     * @param string|array $features a list of meta objects to fetch. null means 
     *                     everything. Can be 
     *                          * 1 string (i.e. '_settings'), 
     *                          * csv (i.e. '_settings,_aliases'), 
     *                          * array (i.e. ['_settings','_aliases']
     * @param array $options can contain: 
     *          ['ignore_unavailable']
     *              (bool) Whether specified concrete indices should be ignored 
     *              when unavailable (missing or closed)
     *          ['allow_no_indices']
     *              (bool) Whether to ignore if a wildcard indices expression 
     *              resolves into no concrete indices. (This includes `_all` 
     *              string or when no indices have been specified)
     *          ['expand_wildcards'] 
     *              (enum) Whether to expand wildcard expression to concrete 
     *              indices that are open, closed or both.
     *          ['local']
     *              (bool) Return local information, do not retrieve the state 
     *              from master node (default: false)
     * @return array
     */
    public function meta($feature = null, array $options = []) {
        
    }

    /**
     * Retreives the meta data of an index
     * @param string|array $features a list of meta objects to fetch. null means 
     *                     everything. Can be 
     *                          * 1 string (i.e. '_settings'), 
     *                          * csv (i.e. '_settings,_aliases'), 
     *                          * array (i.e. ['_settings','_aliases']
     * @param array $options can contain: 
     *          ['ignore_unavailable']
     *              (bool) Whether specified concrete indices should be ignored 
     *              when unavailable (missing or closed)
     *          ['allow_no_indices']
     *              (bool) Whether to ignore if a wildcard indices expression 
     *              resolves into no concrete indices. (This includes `_all` 
     *              string or when no indices have been specified)
     *          ['expand_wildcards'] 
     *              (enum) Whether to expand wildcard expression to concrete 
     *              indices that are open, closed or both.
     *          ['local']
     *              (bool) Return local information, do not retrieve the state 
     *              from master node (default: false)
     * @return array
     */
    public static function meta($feature = null, array $options = []) {
        
    }

    /**
     * Retreives just the settings of the index
     * @param array $options check _meta() for details
     * @return array
     */
    public function metaSettings(array $options = []) {
        
    }

    /**
     * Retreives just the settings of the index
     * @param array $options check _meta() for details
     * @return array
     */
    public static function metaSettings(array $options = []) {
        
    }

    /**
     * Retreives just the aliases of the index
     * @param array $options check _meta() for details
     * @return array
     */
    public function metaAliases(array $options = []) {
        
    }

    /**
     * Retreives just the aliases of the index
     * @param array $options check _meta() for details
     * @return array
     */
    public static function metaAliases(array $options = []) {
        
    }

    /**
     * Retreives just the warmers of the index
     * @param array $options check _meta() for details
     * @return array
     */
    public function metaWarmers(array $options = []) {
        
    }

    /**
     * Retreives just the warmers of the index
     * @param array $options check _meta() for details
     * @return array
     */
    public static function metaWarmers(array $options = []) {
        
    }

    /**
     * Retreives just the mappings of the index
     * @param array $options check _meta() for details
     * @return array
     */
    public function metaMappings(array $options = []) {
        
    }

    /**
     * Retreives just the mappings of the index
     * @param array $options check _meta() for details
     * @return array
     */
    public static function metaMappings(array $options = []) {
        
    }

}
