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

}
