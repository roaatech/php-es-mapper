<?php

namespace Tests;

use ItvisionSy\EsMapper\TypeQueryInterface;
use ItvisionSy\EsMapper\TypeQueryTrait;

class FooTypeQuery extends TestsIndexQuery implements TypeQueryInterface {

    use TypeQueryTrait;

    public function modelClassName() {
        return "Foo";
    }

    public function type() {
        return "foo";
    }

}
