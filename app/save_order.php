<?php
require_once '../core/init.php'; 

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $customer = Cleaner::str($_POST['customer']);
        $email = Cleaner::str($_POST['email']);
        $phone = Cleaner::str($_POST['phone']);
        $address = Cleaner::str($_POST['address']);
        $basket = new Basket();
        $basket->init(); 
        if (empty($basket->getItems())) {
            throw new Exception("Корзина пуста. Невозможно оформить заказ.");
        }
        $order = new Order($customer, $email, $phone, $address, $basket->getItems());
        Eshop::saveOrder($order);
        header('Location: /catalog'); 
        exit();
    } else {
        throw new Exception('Неверный метод запроса.');
    }
} catch (Exception $e) {
    echo 'Ошибка: ' . htmlspecialchars($e->getMessage());
}
