<?php

/* * ***
 * All the examples suppose there is an ES instance with the following 
 * index/type/doc architecture:
 * 
 *  - tests
 *      - foo
 *          - foo1: {name: Fooa, id: 1, age: 33, alive: true}
 *          - foo2: {name: Foob, id: 2, age: 18, alive: false}
 *      - bar
 *          - bar1: {name: Bara, id: 1, age: 33, alive: true}
 *          - bar2: {name: Barb, id: 2, age: 77, alive: false}
 *          - bar3: {name: Barc, id: 3, age: 12, alive: false}
 */

require '../vendor/autoload.php';

require_once 'TestsIndexQuery.php';
require_once 'FooTypeQuery.php';
require_once 'BarTypeQuery.php';
require_once 'FooModel.php';
require_once 'BarModel.php';

$result = Tests\FooTypeQuery::query([
            "size" => 0,
            "aggregations" => [
                "alive" => [
                    "terms" => [
                        "field" => "alive"
                    ]
                ]
            ]
        ]);

var_dump($result->fetch('aggregations.alive'));
