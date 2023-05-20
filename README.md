# Base Use
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

# Controller Use
- Project Root
  - _core
  - app
    - controllers
        - HomeController.php
    - views
        - home
            - index.php
  - .htaccess
  - index.php

###### HomeController.php
```php
namespace App\Controllers;
use DafCore\Controller;

class HomeController extends Controller{
    public function index(){
        return $this->view("index");
    }
}         
```
###### index.php
```php
$app = new Application();

// render view
$app->router->get('/', [HomeController::class, 'index']);
  
$app->run();            
```