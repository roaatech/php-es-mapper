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

use ArrayAccess;
use Iterator;

/**
 * Interface for any result model class
 * 
 * @package ItvisionSy\EsMapper
 * @author Muhannad Shelleh <muhannad.shelleh@itvision-sy.com>
 */
interface IModel extends ArrayAccess, Iterator {

    public static function make(array $array);
}
