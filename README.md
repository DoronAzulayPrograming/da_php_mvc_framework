### Example-1
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
### Example-2
- Project Root
  - _core
  - app
    - views
        - home.php
  - .htaccess
  - index.php

```php
$app = new Application();

// render view
$app->router->get('/', "home");
  
$app->run();            
```
### Example-3
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

// render view
$app->router->get('/', "home/home");
  
$app->run();            
```

