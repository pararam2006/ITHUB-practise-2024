<?php
require_once '../core/init.php'; 
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $itemId = (int)$_POST['item_id']; 
        $quantity = (int)$_POST['quantity']; 
        $basket = new Basket();
        $basket->init();
        $basket->add($itemId, $quantity);
        echo 'Добавление товара в корзину покупателя';
    } else {
        throw new Exception('Неверный метод запроса.');
    }
} catch (Exception $e) {
    echo 'Ошибка: ' . htmlspecialchars($e->getMessage());
}
