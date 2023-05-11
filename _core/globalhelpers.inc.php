<?php 

function cloneArray(array $arr) {
  $clone = [];
  foreach($arr as $k => $v) {
      if(is_array($v)) $clone[$k] = cloneArray($v); //If a subarray
      else if(is_object($v)) $clone[$k] = clone $v; //If an object
      else $clone[$k] = $v; //Other primitive types.
  }
  return $clone;
}

function set_object_vars($object, array $vars, $upperCaseFirst = false)
{
    $has = get_object_vars($object);
    foreach ($has as $name => $oldValue) {
        if($upperCaseFirst)
            $object->$name = isset($vars[ucfirst($name)]) ? $vars[ucfirst($name)] : NULL;
        else $object->$name = isset($vars[$name]) ? $vars[$name] : NULL;
    }
}

function str_replace_first($search, $replace, $subject)
{
    $search = '/'.preg_quote($search, '/').'/';
    return preg_replace($search, $replace, $subject, 1);
}


?>