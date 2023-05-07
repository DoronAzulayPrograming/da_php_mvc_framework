```php
$app = new Application(dirname(__DIR__));
$app->services->addSingleton("db",function(){return new DBContext();});
$app->router->get('', [HomeController::class]);
  
$app->run();            // print 70
```
