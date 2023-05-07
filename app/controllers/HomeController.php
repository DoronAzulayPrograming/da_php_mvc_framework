<?php 

namespace App\Controllers;
use DafCore\Controller;

class HomeController extends Controller{
    public function index(){
        return $this->view("index");
    }
}

?>