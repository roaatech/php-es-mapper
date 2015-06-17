<?php

namespace Tests;

use ItvisionSy\EsMapper\Query;

class TestsIndexQuery extends Query {

    public function index() {
        return "tests";
    }

    public function modelNamespace() {
        return "\\Models\\";
    }

}
