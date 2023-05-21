<?php 
    namespace DafCore;
    class JsonDB {
        private $data;
        private $isAutoId;
        private $filename;

        public function __construct($filename) {
            $this->filename = $filename;
            $this->data['id'] = 0;
            $this->data['collection'] = [];
            $this->isAutoId = false;
            $this->loadData();
        }

        public function autoId():self{
            $this->isAutoId = true;
            return $this;
        }
        
        public function lastInsertId() : int{
            return intval($this->data['id']);
        }

        public function saveData() {
            $json = json_encode($this->data);
            file_put_contents($this->filename, $json);
        }

        public function getAll() {
            return $this->data['collection'];
        }

        public function getById($id) {
            foreach ($this->data['collection'] as $item) {
                if ($item['id'] == $id) {
                    return $item;
                }
            }
            return null;
        }

        public function add($item) {
            
            if($this->isAutoId && is_array($item))
                $item['id'] = $this->genereateId();
            else if($this->isAutoId)
                $item->id = $this->genereateId();

            $this->data['collection'][] = $item;
            $this->saveData();
            return $item;
        }

        public function update($id, $updatedItem) : bool {
            foreach ($this->data['collection'] as &$item) {
                if ($item['id'] == $id) {
                    $item = $updatedItem;
                    $this->saveData();
                    return true;
                }
            }
            return false;
        }

        public function delete($id) : bool {
            foreach ($this->data['collection'] as $key => $item) {
                if ($item['id'] == $id) {
                    unset($this->data['collection'][$key]);
                    $this->saveData();
                    return true;
                }
            }
            return false;
        }


        private function loadData() {
            $json = file_get_contents($this->filename);
            $this->data = json_decode($json, true) ?: [];
        }

        private function genereateId(){
            $this->data['id'] = intval($this->data['id']) + 1;
            return $this->data['id'];
        }

    }

?>