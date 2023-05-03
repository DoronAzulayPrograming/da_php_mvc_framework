<?php 
    namespace App\Models;
    use _Frm_core\DbModel;
    
    class Product extends DbModel{
        public $id;
        public $name;
        public $price;
        public $deliveryTime;

        public function table_schema(){
            return array(
                "table_name" => "Products",
                "id" => "int NOT NULL PRIMARY KEY AUTO_INCREMENT",
                "name" => "varchar(255) NOT NULL",
                "price" => "int NOT NULL",
                "deliveryTime" => "int NOT NULL"
            );
        }
    }
?>