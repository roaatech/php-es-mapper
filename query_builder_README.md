#ElasticSearch DSL Query Builder Class
This is a simple Query Builder for ElasticSearch DSL language

##Installation
It comes with the [php-es-mapper package](http://github.com/itvisionsy/php-es-mapper).
Read about it [here](./README.md).

##How to use
There are two ways to instantiate a builder object.

###Generic query builder
The query builder is not related to a specific TypeQuery class, it helps to build the query array and retrieve it. 
The built array will be then explicitly sent to some query class for execution.
```PHP
//init a builder object
$builder = \ItvisionSy\EsMapper\QueryBuilder::make();

//build the query using different methods
$query = $builder
                ->where('key1','some value') //term clause
                ->where('key2',$intValue,'>') //range clause
                ->where('key3','value','!=') //must_not term clause
                ->where('key4', ['value1','value2']) //terms clause
                ->where('email', '@hotmail.com', '*=') //wildcard search for all @hotmail.com emails
                ->sort('key1','asc') //first sort option
                ->sort('key2',['order'=>'asc','mode'=>'avg']) //second sort option
                ->from(20)->size(20) //results from 20 to 39
                ->toArray();

//modify the query as you need
$query['aggs']=['company'=>['terms'=>['field'=>'company']]];

//then execute it against a type query
$result = TypeQuery::query($query);
//$result is a \ItvisionSy\EsMapper\Result instance
```

###Auto implicit query builder
This type is implicitly created using a `TypeQuery::builder()` method.

It acts like the generic query builder, but in addition to everything the generic query builder can do, 
it allows you to execute the result query directly from within the query builder instant itself.

```PHP
$result = TypeQuery::builder()
    ->where('key1','some value') //term clause
    ->where('key2',$intValue,'>') //range clause
    ->where('key3','value','!=') //must_not term clause
    ->where('key4', ['value1','value2']) //terms clause
    ->where('email', '@hotmail.com', '*=') //wildcard search for all @hotmail.com emails
    ->sort('key1','asc') //first sort option
    ->sort('key2',['order'=>'asc','mode'=>'avg']) //second sort option
    ->from(20)->size(20) //results from 20 to 39
    ->execute();
//$result is a \ItvisionSy\EsMapper\Result instance
```

##Building a query

###The available ElasticSearch methods
The query builder allows the main filter/query clauses: `term`, `match`, `wildcard`, `range`, and `prefix`.
Each of these methods can be used against a key, or multiple keys, or a value, or multiple values, giving
flexible way to build up a really complex queries.

###The smart `where` method
The `::where($key, $value, $compare, $filter)` method is also provided, which will scan, detect, and convert
itself to one of the previous available methods. You can use the $compare to tell it what you are looking for,
and the $filter to enforce a filter or query clause.

###Sorting
The `::sort($key, $order)` allows you to add multiple sort levels. 

The `$order` parameter can be a simple 'desc' or 'asc' value, or can be an assoc array as per ElasticSearch [sort documentation](https://www.elastic.co/guide/en/elasticsearch/reference/1.6/search-request-sort.html).

The third `$override` parameter allows you to clear the sort section before adding the sort terms.


###Pagination
You can use `from`, and/or `size` for detailed control, or use the `page($size, $from)` which will make two different calls to each method alone. 

###Extra methods
If you need to add extra clauses, there are several methods which allow you to: 
`raw`, `rawMustFilter`, `rawMustQuery`, `rawMustNotFilter`, `rawMustNotQuery`, `rawShouldFilter`, and `rawShouldQuery`.

The raw query allows you to add any query/filter clause as an assoc-array to one of the filter/query [bool](https://www.elastic.co/guide/en/elasticsearch/reference/1.6/query-dsl-bool-query.html) clause sections.

###Sub queries

Sub queries will be automatically created when needed. However, you can add subqueries using the `andWhere` and/or `orWhere` methods.

Each of the two methods will start a new subquery, and returns a new query builder object of class `\ItvisionSy\EsMapper\SubQueryBuilder` where you can add extra filter clauses as you want.

When you are done with the subquery, you can finish it and return to the main query by calling the `->endSubQuery()` method.

For now, the subqueries can only be added to the filter bool clause sections.