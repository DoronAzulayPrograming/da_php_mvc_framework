# Base Use
### Example-1
- Project Root
  - _core
  - .htaccess
  - index.php

```php
require_once '_core/autoloader.inc.php';
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
require_once '_core/autoloader.inc.php';

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
require_once '_core/autoloader.inc.php';

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
namespace App;
require_once '_core/autoloader.inc.php';
use App\Controllers;

$app = new Application();

// render view
$app->router->get('/', [HomeController::class, 'index']);
  
$app->run();            
```


# Minimal API
```php
require_once '_core/autoloader.inc.php';

use DafCore\Application;
use DafCore\AutoConstruct;
use DafCore\JsonDB;

class Product extends AutoConstruct{
    public $id;
    public $name;
    public $price;
}

$database = (new JsonDB('products.json'))->autoId();

$app = new Application();

$app->router->get('/api/products', function($res) use ($database){
    return $res->ok($database->getAll());
});
$app->router->get('/api/products/:id', function($res, $id) use ($database){
    return $res->ok($database->getById($id));
});
$app->router->post('/api/products', function($body, $res) use ($database){
    $product = $database->add(new Product(null, $body->name, $body->price));
    return $res->created($product);
});
$app->router->delete('/api/products/:id', function($res, $id) use ($database){
    $database->delete($id);
    return $res->ok($id);
});

$app->run();

```


# API Controllers
###### ProductsController.php
```php
use App\Models\Product;
use DafCore\ApiController;

class ProductsController extends ApiController{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    function getAll(){
        return $this->ok($this->db->getAll());
    }

    function getOne($id){
        return $this->ok($this->db->getById($id));
    }

    function post($body){
        $product = $this->db->add(new Product(null, $body->name, $body->price));
        return $this->created($product);
    }

    function delete($id){
        $this->db->delete($id);
        return $this->ok($id);
    }
}
```
###### index.php
```php
namespace App;
require_once '_core/autoloader.inc.php';

use DafCore\Application;
use DafCore\JsonDB;
use ProductsController;

$app = new Application();
$app->services->addSingleton("db", function(){
    return (new JsonDB('products.json'))->autoId();
});

$app->router->get('/api/products', [ProductsController::class, 'getAll']);
$app->router->get('/api/products/:id', [ProductsController::class, 'getOne']);
$app->router->post('/api/products', [ProductsController::class, 'post']);
$app->router->delete('/api/products/:id', [ProductsController::class, 'delete']);

$app->run();
```