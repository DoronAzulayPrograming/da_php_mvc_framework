<?php 
namespace App;

use _Frm_core\Application;
use App\Controllers\HomeController;
use App\Controllers\UsersController;
use App\Core\DBContext;

define("db_servername", "localhost");
define("db_username", "doron_mt_test");
define("db_password", "test123");
define("db_database", "doron_mt_test");
define("db_charset", "utf8");
define("db_port", 3306);

require_once '_frm_core/autoloader.inc.php';

try {

    // $db->Products->update(new Product(3,"sufa-1",999,999))->where("id","=",3)->execute();
    // $list = $db->Products->fetchAll();
    // $one = $db->Products->single("id", "=", 1);
    $app = new Application(__DIR__);

    $app->add_singleton("db",function(){return new DBContext();});
    
    $app->router->get('',[HomeController::class]);
    $app->router->get('login',[UsersController::class,'login']);
    $app->router->get('api/products',function($db, $res){
        return $res->json_stringify($db->Products->fetchAll());
    });
    
    $app->run();

} catch (\Throwable $th) {
    echo "" . $th->getMessage();
}


?>