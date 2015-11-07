<?php

namespace Tests;

use ItvisionSy\EsMapper\TypeQueryInterface;
use ItvisionSy\EsMapper\TypeQueryTrait;

class ZeeTypeQuery extends TestsIndexQuery implements TypeQueryInterface {

    use TypeQueryTrait;

    public function modelClassName() {
        return "Zee";
    }

    public function type() {
        return "zee";
    }

    protected function additionalQuery() {
        return [
            'query' => [
                'filtered' => [
                    'filter' => [
                        'term' => ['alive' => false]
                    ]
                ]
            ]
        ];
    }

}
