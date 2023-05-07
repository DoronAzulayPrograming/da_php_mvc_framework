<?php 
namespace App;

require_once '_core/autoloader.inc.php';

use DafCore\Application;
use App\Core\DBContext;
use App\Controllers\HomeController;

try {
    
    $app = new Application(dirname(__DIR__));
    $app->services->addSingleton("db",function(){return new DBContext();});
    $app->router->get('', [HomeController::class]);
    
    $app->run();

} catch (\Throwable $th) {
    echo "" . $th->getMessage();
}

?>