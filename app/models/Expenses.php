<?php 
    namespace App\Models;
    use _Frm_core\DbModel;

    class Expenses extends DbModel{
        public $id;
        public $name;
        public $price;
        public $orderId;
    
        public function table_schema(){
            return array(
                "table_name" => "Expenses",
                "id" => "int NOT NULL PRIMARY KEY AUTO_INCREMENT",
                "name" => "varchar(255) NOT NULL",
                "price" => "int NOT NULL",
                "orderId" => "int NOT NULL"
            );
        }
    }
?>