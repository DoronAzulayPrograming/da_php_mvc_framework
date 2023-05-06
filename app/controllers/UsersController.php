<?php 

namespace App\Controllers;
use DafCore\Controller;
use App\Models\User;

class UsersController extends Controller{

    private $jwtService;

    public function __construct($jwtService)
    {
      $this->jwtService = $jwtService;
    }

    public function login($username, $password) {
      // check username and password in database
      $user = new User(1, "admin", "123");
  
      if ($user) {
        // generate JWT token with user ID as payload
        
        $payload = array(
            "user_id" => $user->id,
            "email" => $user->email
        );
        
        $jwt = $this->jwtService->generateToken($payload);

        // set JWT token as header
        header('Authorization: Bearer ' . $jwt);
        
        return $jwt;
      } else {
        return "Invalid username or password.";
      }
    }
}

?>