<?php

require '../vendor/autoload.php';

$builder = \ItvisionSy\EsMapper\QueryBuilder::make();
/* @var $builder \ItvisionSy\EsMapper\QueryBuilder */
var_dump($builder->toJSON());

$builder->emptyQuery();
$builder->term("name", "Muhannad Shelleh");
var_dump($builder->toJSON());

$builder->emptyQuery();
$builder->term(["name", "email"], "Muhannad Shelleh");
var_dump($builder->toJSON());

$builder->emptyQuery();
$builder->term(["name", "email"], ["Muhannad", "Shelleh"]);
var_dump($builder->toJSON());

$builder->emptyQuery();
$builder->orWhere()->where("name", "Shehab", "*")->where("email", "mhh1422", "*")->endSubQuery();
var_dump($builder->toJSON());

$builder->emptyQuery();
$builder->orWhere()->where("name", "Muhannad Shelleh")->where("email", "muhannad.shelleh@live.com")->endSubQuery();
var_dump($builder->toJSON());

$builder->emptyQuery();
$builder->orWhere()->where("name", "Muhannad", "=*")->where("email", "*hotmail*", "*=*")->endSubQuery();
var_dump($builder->toJSON());
