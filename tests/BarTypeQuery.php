<?php

namespace Tests;

use ItvisionSy\EsMapper\TypeQueryInterface;
use ItvisionSy\EsMapper\TypeQueryTrait;

class BarTypeQuery extends TestsIndexQuery implements TypeQueryInterface {

    use TypeQueryTrait;

    public function modelClass() {
        return "Bar";
    }

    public function type() {
        return "bar";
    }

}
