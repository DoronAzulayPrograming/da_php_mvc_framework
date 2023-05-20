# Example-1
- Project Root
  - _core
  - .htaccess
  - index.php

```php
$app = new Application();

// render functions
$app->router->get('/', function(){
    // code...
    return "<h1>Hello World.!!!</h1>";
});
  
$app->run();            
```
# Example-2
- Project Root
  - _core
  - app
    - views
        - home.php
  - .htaccess
  - index.php

```php
$app = new Application();

// render functions
$app->router->get('/', function(){
    // code...
    return "home";
});
  
$app->run();            
```
# Example-3
- Project Root
  - _core
  - app
    - views
        - home
            - home.php
  - .htaccess
  - index.php

```php
$app = new Application();

// render functions
$app->router->get('/', function(){
    // code...
    return "home/home";
});
  
$app->run();            
```

