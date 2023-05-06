<?php 

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
