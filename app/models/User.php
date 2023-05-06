<?php 
    namespace App\Models;
    use DafCore\AutoConstruct;
    
    class User extends AutoConstruct{
        public $id;
        public $email;
        public $password;
    }
?>