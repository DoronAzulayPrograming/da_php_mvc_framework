<?php 
namespace DafCore;
class ServiceCollection{
    
    private $services = [];

    public function serviceExists($key){
        if (!array_key_exists($key, $this->services)) {
            return false;
        }
        return true;
    }

    public function getService($key) {
        if (!array_key_exists($key, $this->services)) {
            throw new \Exception("Service $key not found");
        }

        $service = $this->services[$key];
        if($service->is_singleton)
            return $this->services[$key]->dependency;

        return ($this->services[$key]->dependency)($this);
    }

    public function getServices($keys) : array {
        $result = array();
        foreach ($keys as $key) {
            if(array_key_exists($key, $this->services)){
                $service = $this->services[$key];
                if($service->is_singleton)
                    array_push($result, $service->dependency);
                else {
                    array_push($result, ($service->dependency)($this));
                }
            }
        }
        return $result;
    }

    public function addScop($key, $callback){
        $service = new \stdClass;
        $service->is_singleton = false;
        $service->dependency = $callback;
        $this->services[$key] = $service;
    }

    public function addSingleton($key, $callback){
        $service = new \stdClass;
        $service->is_singleton = true;
        $service->dependency = $callback($this);
        $this->services[$key] = $service;
    }
}
?>