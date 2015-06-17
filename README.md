#ElasticSearch Mapper (es-mapper)
This is a simple ORM mapper for ElasticSearch on PHP.

##How to use?
That is simple:

### Per index query:
 1. Create a class extending the main query class (for general index use) .
 1. Fill in the abstract methods. They are self-descriptive.
 1. Use the created class ::find($type, $id), ::query($type, array $query =[]), and ::all($type)
    You will get a list of Model objects where you can object-property access to get all the info.
    i.e. $model->name to get the name property, ...

### Per type query
 1. Create a class extending the type query class.
    OR create a class extending the main query class and implement the TypeQueryInterface interface and use the TypeQueryTrait trait
 1. Fill in the abstract methods. They are self-descriptive.
 1. Use the methods: ::find($id), ::all(), and ::query(array $query=[]).
    You will get a list of Model objects the same way described above.

### Extending the Model class
You can extend the Model class easily. Just extend it!
In case you were using the namespaces, you can set the models namespace in the query class by overriding the modelNamespace public method. This method should return a string ending with \

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