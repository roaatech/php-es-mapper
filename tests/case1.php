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

//find multiple models
$ids = ['foo0', 'foo1', 'foo2', 'foo3', 'foo4'];
$models = Tests\TestsIndexQuery::find('foo', $ids);
echo "{$models->count()} documents found out of " . count($ids) . " total ids!<br />";
foreach ($models as $id => $model) {
    dump_me($id, $model, "I am found through mget method internally!");
}

//find multiple models
$ids = ['foo0', 'foo1', 'foo2', 'foo3', 'foo4'];
$models = Tests\FooTypeQuery::find($ids);
echo "{$models->count()} documents found out of " . count($ids) . " total ids using the type query!<br />";
foreach ($models as $id => $model) {
    dump_me($id, $model, "I am found through mget method internally, but using the type query instead!!");
}


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
    dump_me($id, $model, "My score is {$model->score} and I am " . ($model->alive ? "alive" : "dead"));
    $model->update(['doc' => ['alive' => !$model->alive] + $model->attributes]);
}