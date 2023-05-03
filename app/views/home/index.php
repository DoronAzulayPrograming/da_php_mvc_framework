<?php 

use \da_db\BaseContext;
use App\Models\Product;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class DaContext extends BaseContext{

    public $Products;
    public function __construct() {
        parent::__construct(db_servername,db_port,db_database,db_username,db_password,10);

        $this->Products = (new \da_db\DBSet($this, Product::class, "Products"))
        ->drop()->execute()
        ->addColumn('id', 'INT', null, false, null, true)
        ->addColumn('name', 'VARCHAR', 255, false)
        ->addColumn('price', 'INT', null, false, 0)
        ->addColumn('deliveryTime', 'INT', null, false, 0)
        ->addPrimaryKey('id')
        ->execute();
    }

}
//$db = new DaContext();
// $db->table("Products")->insert(new Product('',"milki",222,222))->execute();
// $db->table("Products")->insert(new Product('',"laydi",111,111))->execute();
// $db->table("Products")->insert(new Product('',"sufa",333,333))->execute();
// $db->table("Products")->update(new Product(3,"sufa-1",999,999))->where("id","=",3)->execute();
// $list = $db->table("Products")->select()->execute()->fetchAllAs(Product::class);
// $one = $db->table("Products")->select()->where("id", "=", 1)->execute()->fetchAs(Product::class);

// $db->Products->insert(new Product('',"milki",222,222))->execute();
// $db->Products->insert(new Product('',"laydi",111,111))->execute();
// $db->Products->insert(new Product('',"sufa",333,333))->execute();
// $db->Products->update(new Product(3,"sufa-1",999,999))->where("id","=",3)->execute();
// $list = $db->Products->fetchAll();
// $one = $db->Products->single("id", "=", 1);

// try {

//     $secret_key = "my_secret_key";
//     $algorithm = 'HS256';
    
//     $payload = array(
//         "user_id" => 123,
//         "email" => "john@example.com"
//     );
    
//     $jwt = JWT::encode($payload, $secret_key, $algorithm);
//     $decoded = JWT::decode($jwt, new Key($secret_key, $algorithm));
//     print_r($decoded);


// } catch (\Throwable $th) {
//     echo "" . $th->getMessage();
// }

// $headers = apache_request_headers();
// if (isset($headers["Authorization"]) && strpos($headers["Authorization"], 'Bearer') !== false) {
//     // extract JWT token from Authorization header
//     $jwt = str_replace('Bearer ', '', $headers["Authorization"]);
//     echo $jwt;
// }
?>



<h1>שולחן עבודה </h1>
<?=$one->name ?> - <?=$one->price ?>
<ul>
<?php foreach ($list as $key => $value) { ?>
    <li>
        <?=$value->name ?> - <?=$value->price ?>
    </li>
<? } ?>
</ul>
