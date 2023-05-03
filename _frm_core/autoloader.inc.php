<?php
require_once '_frm_core/globalhelpers.inc.php';

spl_autoload_register('myAutoLoader');

function myAutoLoader($name){
    $name = explode_class_name($name);
    load("_frm_core/",$name);
    load("_frm_core/vendor/",$name);
    load("app/core/",$name);
    load("app/models/",$name);
    load("app/viewModels/",$name);
    load("app/services/",$name);
    load("app/controllers/",$name);
}

// function explode_class_name($name){
//     if(strpos("\\", $name) !== false) 
//         return $name;

//     return end(explode("\\", $name));    
// }

function explode_class_name($class){
    if(strpos("\\", $class) !== false) 
        return $class;

    return basename(str_replace('\\', '/', $class)); 
}

function load($path, $className){
    $extension = ".php";
    $fullPath = $path . $className . $extension;

    if(!file_exists($fullPath)){
        return false;
    }

    include_once $fullPath;
}

?>