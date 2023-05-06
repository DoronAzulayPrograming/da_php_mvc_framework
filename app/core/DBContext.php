<?php

namespace App\Core;

use App\Models\Product;
use DafCore\Db\DBSet;
use DafCore\Db\BaseContext;

class DBContext extends BaseContext
{
    public $Products;
    public function __construct() {
        parent::__construct("localhost",3306,"doron_mt_test","doron_mt_test","test123",10);

        $this->Products = (new DBSet($this, Product::class, "Products"))
        //->drop()->execute()
        ->addColumn('id', 'INT', null, false, null, true)
        ->addColumn('name', 'VARCHAR', 255, false)
        ->addColumn('price', 'INT', null, false, 0)
        ->addColumn('deliveryTime', 'INT', null, false, 0)
        ->addPrimaryKey('id')
        ->execute();
    }
}
?>