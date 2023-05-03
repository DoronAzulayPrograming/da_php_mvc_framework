<?php 

namespace App\Controllers;
use _Frm_core\Controller;
use App\Models\User;

use Firebase\JWT\JWT;

class UsersController extends Controller{

    private $secret_key = "my_secret_key"; // your secret key for JWT
    private $algorithm = "HS256"; // your algorithm for JWT

    public function login($username, $password) {
      // check username and password in database
      $user = new User();
      $user->id = 1;
      $user->email = "admin";
      $user->password = "123";
  
      if ($user) {
        // generate JWT token with user ID as payload
        
        $payload = array(
            "user_id" => $user->id,
            "email" => $user->email
        );
        
        $jwt = JWT::encode($payload, $this->secret_key, $this->algorithm);

        // set JWT token as header
        header('Authorization: Bearer ' . $jwt);
        
        // return JWT token to client
        return $jwt;
      } else {
        // return error message to client
        return "Invalid username or password.";
      }
    }
}

?>