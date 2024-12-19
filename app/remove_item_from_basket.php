<?php
require_once '../core/init.php';
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $itemId = (int)$_POST['item_id'];
        $basket = new Basket();
        $basket->init(); 
        $basket->remove($itemId);
        echo 'Удаление товара из корзины';
    } else {
        throw new Exception('Неверный метод запроса.');
    }
} catch (Exception $e) {
    echo 'Ошибка: ' . htmlspecialchars($e->getMessage());
}
