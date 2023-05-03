<?php

namespace App\Core;

use App\Models\Product;
use da_db\BaseContext;

class DBContext extends BaseContext
{
    public $Products;
    public function __construct() {
        parent::__construct(db_servername,db_port,db_database,db_username,db_password,10);

        $this->Products = (new \da_db\DBSet($this, Product::class, "Products"))
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