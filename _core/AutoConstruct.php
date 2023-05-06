<?php 
namespace DafCore;

abstract class AutoConstruct
{
    public function __construct()
    {
        $arguments = func_get_args();
        $numberOfArguments = func_num_args();

        $constructor = method_exists(
            $this,
            $fn = "__construct" . $numberOfArguments
        );

        if ($numberOfArguments < 1)
            return;
            
        if ($constructor) {
            call_user_func_array([$this, $fn], $arguments);
        } else {
            $args_count = count($arguments);
            $index = 0;
            foreach ($this as $key => $value) {
                $this->$key = $arguments[$index++];
                if ($args_count == $index)
                    break;
            }
        }
    }
    public function __construct0()
    {
    }
}

?>