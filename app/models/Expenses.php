<?php 
    namespace App\Models;
    use DafCore\AutoConstruct;

    class Expenses extends AutoConstruct{
        public $id;
        public $name;
        public $price;
        public $orderId;
    }
?>