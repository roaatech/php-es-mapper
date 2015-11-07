#ElasticSearch Mapper (es-mapper)
This is a simple ORM mapper for ElasticSearch on PHP.

##Installation
###Composer
```composer require itvisionsy/php-es-orm```

please note it requires Elasticsearch PHP SDK v>=1 and <2

##How to use?
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
 * `::find(scalar)` and `::find(scalar[])` methods are mapped to [get](https://github.com/elastic/elasticsearch-php/blob/master/src/Elasticsearch/Client.php#L167) and [mget](https://github.com/elastic/elasticsearch-php/blob/master/src/Elasticsearch/Client.php#L671) methods respectively.
 * `::query` method is mapped to the [search](https://github.com/elastic/elasticsearch-php/blob/master/src/Elasticsearch/Client.php#L1002) method, and the $query param will be passed as is after appending the index and type parameters to it.

##Creating new documents
You can create a new document by calling the create method in different contexts similar to other retrieve methods.
 1. ```IndexQuery::create(array $data, $type, $id=null, array $parameters=[])```
 1. ```TypeQuery::create(array $data, $id=null, array $parameters=[])```

##Updating a document
You can update an already indexed document by:
 1. Either *Re-indexing* a document with the same type and id, OR
 1. Or `update(array $data, array $parameters=[])` method on the model's object:
    ```PHP
    TypeQuery::find(1)->update(['new_key'=>'value','old_key'=>'new value'],[]);
    ```

##Deleting a document
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

##Retrieving results
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

##Examples
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