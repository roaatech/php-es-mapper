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
 * A result set for multi find queries. 
 * It provides the same methods the normal results provide.
 *
 * @package ItvisionSy\EsMapper
 * @author Muhannad Shelleh <muhannad.shelleh@itvision-sy.com>
 */
class MultiGetResult extends Result {

    /**
     * Returns the number of the hits
     * 
     * @return integer
     */
    public function count() {
        return count($this->result['docs']);
    }

    /**
     * Returns the highest score in the hists
     * 
     * @return float
     */
    public function score() {
        return 1;
    }

    /**
     * Returns the raw data array in the hist.
     * This is the raw returned hits list from ES.
     * 
     * @return array
     */
    public function data() {
        return $this->result;
    }

    /**
     * Returns the current array index or document object at the offset.
     * 
     * @see Result::useIndexKeys() 
     * @see Result::useDocumentKeys()
     * @return mixed
     */
    public function key() {
        if ($this->indexKeys) {
            return $this->currentIndex;
        } else {
            return $this->result['docs'][$this->currentIndex]['_id'];
        }
    }

    /**
     * Returns true if the $offset exsits in the results array, false otherwise.
     * IT uses the array index keys for the offset.
     * 
     * @param integer $offset
     * @return boolean
     */
    public function offsetExists($offset) {
        return array_key_exists($offset, $this->result['docs']);
    }

    /**
     * Gets the document object in the $offset
     * 
     * @param integer $offset
     * @return Model
     */
    public function offsetGet($offset) {
        return Model::makeOfType($this->result['docs'][$offset], $this->modelClass);
    }

}
