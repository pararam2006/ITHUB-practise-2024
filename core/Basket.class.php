<?php
class Basket {
    private $items = []; 
    private const COOKIE_NAME = 'eshop'; 
    public function init() {
        if (isset($_COOKIE[self::COOKIE_NAME])) {
            $this->read(); 
        } else {
            $this->create(); 
        }
    }
    public function add($itemId, $quantity) {
        if (isset($this->items[$itemId])) {
            $this->items[$itemId] += $quantity; 
        } else {
            $this->items[$itemId] = $quantity; 
        }
        $this->save(); 
    }
    public function remove($itemId) {
        if (isset($this->items[$itemId])) {
            unset($this->items[$itemId]); 
            $this->save(); 
        }
    }
    public function save() {
        setcookie(self::COOKIE_NAME, json_encode($this->items), time() + 86400, '/'); 
    }
    public function create() {
        
        $this->items = [];
        $this->save(); 
    }
    public function read() {
        $this->items = json_decode($_COOKIE[self::COOKIE_NAME], true);
    }
    public function getItems() {
        return $this->items; 
    }
}
