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
require_once 'ZeeTypeQuery.php';

function dump_me($id, $model, $extra = null) {
    if ($model) {
        $class = get_class($model);
        echo "{$id} ({$class}): I am {$model->name}. My id is {$model->id}, and I am {$model['age']} years old! $extra<br />";
    } else {
        echo "{$id} (null): I am not there!<br />";
    }
}

//get all of type
foreach (\Tests\ZeeTypeQuery::all() as $id => $model) {
    dump_me($id, $model);
}
