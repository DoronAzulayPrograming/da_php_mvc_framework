```php
$app = new Application();

// render functions
$app->router->get('/', function(){
    return "<h1>Hello World.!!!</h1>";
});
  
$app->run();            
```
