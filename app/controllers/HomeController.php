<?php 

namespace App\Controllers;
use DafCore\Controller;

class HomeController extends Controller{

    private $productsService;
    public function __construct($productsService)
    {
        $this->productsService = $productsService;
    }

    public function index(){
        return $this->view("index");
    }
}

?>