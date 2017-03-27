# ElasticSearch PHP ORM and Query Builder (es-mapper)
This is a simple ORM mapper for ElasticSearch for PHP.

[ElasticSearch DSL query builder for PHP](./query_builder_README.md).

## Collaborators required
If you can join me in updating and maintaining this project, please send a message to
muhannad.shelleh@live.com

## Requirements
 - PHP 5.4+
 - Elasticsearch PHP SDK v>=1 and <2
 - ElasticSearch server 1.6. ES2 is not tested, so use with care.

## Installation
### Composer
```composer require itvisionsy/php-es-orm```

### Manual download
Head to the latest version [here](https://github.com/itvisionsy/php-es-mapper/releases/latest) then download using one download button.

## How to use?

**For the Query Builder, [read this README instead](./query_biulder_README.md)**

That is simple:

### Per index query:
 1. Create a class extending the main query class (for general index use) .
 1. Fill in the abstract methods. They are self-descriptive.
 1. Use the created class `::find($type, $id)`, `::query($type, array $query =[])`, and `::all($type)`
    You will get a list of Model objects where you can object-property access to get all the info.
    i.e. `$model->name` to get the name property, ...

### Per type query
 1. Create a class extending the type query class.
    OR create a class extending the main query class and implementing the `TypeQueryInterface` interface and use the `TypeQueryTrait` trait
 1. Fill in the abstract methods. They are self-descriptive.
 1. Use the methods: `::find($id)`, `::all()`, and `::query(array $query=[])`.
    You will get a list of Model objects the same way described above.

#### Please note
Methods' parameters are mapped to original elasticsearch methods and parameters as follows:
 * `::find(scalar)` and `::find(scalar[])` methods are mapped to [get](https://github.com/elastic/elasticsearch-php/blob/master/src/Elasticsearch/Client.php# L167) and [mget](https://github.com/elastic/elasticsearch-php/blob/master/src/Elasticsearch/Client.php# L671) methods respectively.
 * `::query` method is mapped to the [search](https://github.com/elastic/elasticsearch-php/blob/master/src/Elasticsearch/Client.php# L1002) method, and the $query param will be passed as is after appending the index and type parameters to it.

### Querying for data
The query class is just a simple interface allows you to send DSL queries, or perform other ElasticSearch requests.
The `::query()` method for example will expect to receive an assoc-array with a well-formed DSL query.

However, you can use the query builder to builder the query and get a well-formed DSL array out of it. 

You can use a type-query query builder to build the query and execute it directly:
```PHP
$result = TypeQuery::builder()
    ->where('key1','some value')
    ->where('key2',$intValue,'>')
    ->where('key3','value','!=')
    ->where('key4', ['value1','value2'])
    ->execute();
//$result is a \ItvisionSy\EsMapper\Result instance
```

Or you can use a generic query builder to build the query then you can modify it using other tools:
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

Please refer to [this file](./query_builder_README.md) for more detailed information.

## Retrieving results
The returned result set implements the ArrayAccess interface to access specific document inside the result. i.e.
```PHP
$result = SomeQuery::all();
```
You can then get a document like this:
```PHP
$doc = $result[1]; //gets the second document
```
Or you can use the dot notation like that:
```
$result->fetch('hits.hits.0'); //for any absolute access
```

## Accessing document data
On the model object, you can access the results in many ways:
 1. using the object attribute accessor `$object->attribute`
    - if the attribute starts with underscore (_) then it will try to fetch it first from the meta information, then the attributes, and then from the internal object properties.
    - if the attribute starts with two underscores (__) then it will try to fetch first from the internal object properties, then attributes, then meta.
    - if not precedence underscores, then it will try to fetch from the attributes, then meta, then internal object properties.
 1. using the `$object->getAttributes()[attribute]`, as the getAttributes() will return the document data as an array (first level only).
 1. using the `$object->getAttributes($attribute1, $attribute2, ...)` which will return a single (or array) value[s] depending on the requested attributes

## Creating new documents
Either way will work:
 1. Use the index query static method
    ```php
    IndexQuery::create(array $data, $type, $id=null, array $parameters=[])
    ```
    
 1. Use the type query static method:
   ```php
   TypeQuery::create(array $data, $id=null, array $parameters=[])
   ```

## Updating a document
You can update an already indexed document by:
 1. Either *Re-indexing* a document with the same type and id, OR
 1. Or `update(array $data, array $parameters=[])` method on the model's object:
   
   ```php
   TypeQuery::find(1)->update(['new_key'=>'value','old_key'=>'new value'],[]);
   ```

## Deleting a document
The same way you can update a document, you can delete it:
 1. Calling the static method `::delete($type, $id)` on the index query
 1. Calling the method `->delete()` on model's object.

## Adding extra methods
You may need to add extra custom methods like `top($numOfDocs)` or anything else.
To do so, you need to create the method name you wish as protected in the query sub-class. The name should be prefixed with _ (i.e. `_top`) then, you can either
 * Call it prefixed with `get`, so to call the `_top(500)` method, just call `getTop(500)` and it will be mapped as public static and as public. 
 * Override the `_allowPublicAccess` static protected method to add extra methods to expose. 
    Please note that when overriding, don't forget to merge with the parent results not to lose the old ones:
    ```PHP
    protected _allowPublicAccess(){
        return array_merge(parent::_allowPublicAccess(), ['top','any',...]);
    }
    ```

    This way you will save the allowed methods from the parent.

### Extending the Model class
You can extend the Model class easily. Just extend it!
In case you were using the namespaces, you can set the models namespace in the query class by overriding the modelNamespace public method. This method should return a string ending with \
After that, you need to call the `->setModelClass($class)` on the query result object.

## Examples
Please check [tests/](/tests) folder. Basically, the case1.php is the main file.

```
\
|
+-- TestsIndexQuery (TestsIndexQuery.php)   Main index query class.
|   |                                       Maps results to \Models\ namespace.
|   |
|   +-- \FooTypeQuery (FooTypeQuery.php)    Type index query class.
|   |
|   +-- \BarTypeQuery (BarTypeQuery.php)    Type index query class. 
|
|-- Models\
    |
    +-- Foo (FooMode.php)                   Foo model class
    |
    +-- Bar (BarModel.php)                  Bar model class
```

## License
This code is published under [MIT](LICENSE) license.
