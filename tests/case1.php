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
 *          - bar1: {name: 
 *          - bar2
 */

require '../vendor/autoload.php';

require_once 'TestsIndexQuery.php';
require_once 'FooTypeQuery.php';
require_once 'BarTypeQuery.php';
require_once 'FooModel.php';
require_once 'BarModel.php';

function dump_me($id, $model, $extra = null) {
    if ($model) {
        $class = get_class($model);
        echo "{$id} ({$class}): I am {$model->name}. My id is {$model->id}, and I am {$model['age']} years old! $extra<br />";
    } else {
        echo "{$id} (null): I am not there!<br />";
    }
}

//get all of type
foreach (\Tests\FooTypeQuery::all() as $id => $model) {
    dump_me($id, $model);
}

//find model by id
$ids = ['bar1', 'bar0'];
foreach ($ids as $id) {
    $model = \Tests\BarTypeQuery::find($id);
    dump_me($id, $model);
}

//find model by type and id
$id = 'foo1';
$model = Tests\TestsIndexQuery::find('foo', $id);
dump_me($id, $model);

//find model by type and id for unclassed one
$id = 'zee1';
$model = Tests\TestsIndexQuery::find('zee', $id);
dump_me($id, $model);


//get models by query of type
foreach (\Tests\FooTypeQuery::query([
    "query" => [
        "range" => [
            "age" => [
                "gte" => 30
            ]
        ]
    ]
]) as $id => $model) {
    dump_me($id, $model);
}

//get models by query for all types
foreach (\Tests\TestsIndexQuery::query(null, [
    "query" => [
        "range" => [
            "age" => [
                "gte" => 30
            ]
        ]
    ]
]) as $id => $model) {
    dump_me($id, $model, "My score is {$model->score}");
}