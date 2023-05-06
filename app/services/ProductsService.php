<?php 

namespace App\Services;

class ProductsService{

    private $db;
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function get_all(){
        echo "heee";
    }
}

?>