<?php 
    namespace App\Models;
    use DafCore\AutoConstruct;
    
    class Product extends AutoConstruct{
        public $id;
        public $name;
        public $price;
        public $deliveryTime;
    }
?>