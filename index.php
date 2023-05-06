<?php 
namespace App;

require_once '_core/autoloader.inc.php';

use DafCore\Application;
use App\Core\DBContext;
use App\Services\JwtService;
use App\Services\ProductsService;
use App\Controllers\HomeController;
use App\Controllers\UsersController;
use App\Middlewheres\JwtMiddlewhere;

try {
    // $db->Products->update(new Product(3,"sufa-1",999,999))->where("id","=",3)->execute();
    // $list = $db->Products->fetchAll();
    // $one = $db->Products->single("id", "=", 1);
    $app = new Application(dirname(__DIR__));
    $app->autoload_dirs(["vendor/"]);

    $app->services->addSingleton("db",function(){return new DBContext();});
    $app->services->addSingleton("jwt_algorithm",function(){return "HS256";});
    $app->services->addSingleton("jwt_secret_key",function(){return "my_secret_key";});
    $app->services->addSingleton("jwtService",function(){return new JwtService("my_secret_key","HS256");});
    $app->services->addSingleton("productsService",function($x){return new ProductsService($x->getService("db"));});
    $app->services->addSingleton("jwtMiddlewhere",function($x){return new JwtMiddlewhere($x->getService("jwtService"));});
    
    $auth_method = [$app->services->getService("jwtMiddlewhere"), 'auth'];
    $auth_admin = [$auth_method, ['admin']];
    //$app->router->use($auth);

    $app->router->get('', [HomeController::class]);
    $app->router->get('login', [UsersController::class,'login']);
    $app->router->get('api/products', function($db, $res){
        return $res->send($db->Products->fetchAll(), true);
    });
    
    $app->run();

} catch (\Throwable $th) {
    echo "" . $th->getMessage();
}

?>