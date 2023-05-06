<?php

use DafCore\Application;

require_once '_core/globalhelpers.inc.php';

spl_autoload_register('myAutoLoader');

function myAutoLoader($name){
    $name = explode_class_name($name);
    load_recursion("_core", $name);
    load_recursion("app", $name);

    if(!empty(Application::$app)){
        $dirs = Application::$app->get_autoload_dirs();
        foreach ($dirs as $key => $dir) {
            load($dir ,$name);
        }
    }
}

function load_recursion($path, $class_name){
    if(load("$path/" ,$class_name))
        return;
    $scan = scandir($path);
    foreach($scan as $file) {
        if($file === "." || $file === "..") continue;
        if (is_dir("$path/$file")) {
            if(load("$path/$file/" ,$class_name))
                break;
            load_recursion("$path/$file", $class_name);
        }
    }
}

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

    return true;
}

?>