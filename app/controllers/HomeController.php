<?php 

namespace App\Controllers;
use _Frm_core\Controller;

class HomeController extends Controller{

    public function index(){
        return $this->view("index");
    }
}

?>