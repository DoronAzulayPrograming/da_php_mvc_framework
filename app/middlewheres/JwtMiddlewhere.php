<?php 

namespace App\Middlewheres;

class JwtMiddlewhere{

    private $jwtService;

    public function __construct($jwtService)
    {
      $this->jwtService = $jwtService;
    }
    
    public function auth($res, $roles){
        print_r($roles);
        $headers = apache_request_headers();
        if (isset($headers["Authorization"]) && strpos($headers["Authorization"], 'Bearer') !== false) {
            // extract JWT token from Authorization header
            $jwt = str_replace('Bearer ', '', $headers["Authorization"]);

            $claims = $this->jwtService->validateToken($jwt);
            print_r($claims);
        }else{
            $res->status(401)->send("You Are Unauthorized");
            return false;
        }
    }
}

?>