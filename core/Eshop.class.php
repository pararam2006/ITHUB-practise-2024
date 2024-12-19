<?php
class Eshop {
    private static $db;
    public static function init(array $dbConfig) {
        if (empty($dbConfig['HOST']) || empty($dbConfig['USER']) || empty($dbConfig['PASS']) || empty($dbConfig['NAME'])) {
            throw new Exception("Недостаточно данных для подключения к базе данных.");
        }
        self::$db = new mysqli($dbConfig['HOST'], $dbConfig['USER'], $dbConfig['PASS'], $dbConfig['NAME']);
        if (self::$db->connect_error) {
            throw new Exception("Ошибка подключения: " . self::$db->connect_error);
        }
        self::$db->set_charset("utf8");
    }
    public static function getDb() {
        return self::$db;
    }
    public static function addItemToCatalog(Book $book) {
        $title = $book->getTitle();
        $author = $book->getAuthor();
        $pubyear = $book->getPubyear();
        $price = $book->getPrice();
        $stmt = self::getDb()->prepare("CALL spAddItemToCatalog(?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssds", $title, $author, $pubyear, $price);
            if ($stmt->execute()) {
                return true; 
            } else {
                throw new Exception("Ошибка при добавлении товара: " . self::getDb()->error);
            }
            $stmt->close();
        } else {
            throw new Exception("Ошибка подготовки запроса: " . self::getDb()->error);
        }
    }
    public static function getItemsFromCatalog() {
        $books = [];
        $stmt = self::getDb()->prepare("CALL spGetCatalog()");
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $books[] = new Book($row['title'], $row['author'], $row['pubyear'], $row['price']);
            }
            $stmt->close();
        } else {
            throw new Exception("Ошибка подготовки запроса: " . self::getDb()->error);
        }
        return new IteratorIterator(new ArrayIterator($books));
    }
    public static function addItemToBasket($itemId, $quantity) {
        $basket = new Basket();
        $basket->init(); 
        $basket->add($itemId, $quantity);
        echo 'Добавление товара в корзину';
    }
    public static function removeItemFromBasket($itemId) {
        $basket = new Basket();
        $basket->init(); 
        $basket->remove($itemId);
        echo 'Удаление товара из корзины';
    }
    public static function getItemsFromBasket() {
        $basket = new Basket();
        $basket->init(); 

        return $basket->getItems();
    }
    public static function saveOrder(Order $order) {
        $customer = $order->getCustomer();
        $email = $order->getEmail();
        $phone = $order->getPhone();
        $address = $order->getAddress();
        self::getDb()->begin_transaction(); 
        try {
            $stmt = self::getDb()->prepare("CALL spSaveOrder(?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sssss", $customer, $email, $phone, $address);
                if (!$stmt->execute()) {
                    throw new Exception("Ошибка при сохранении заказа: " . self::getDb()->error);
                }
                $orderId = self::getDb()->insert_id;
                $stmt->close();
                foreach ($order->getItems() as $itemId => $quantity) {
                    self::saveOrderedItems($orderId, (int)$itemId, (int)$quantity);
                }
                (new Basket())->create(); 
                self::getDb()->commit(); 
                return true;
            } 
            else {
                throw new Exception("Ошибка подготовки запроса: " . self::getDb()->error);
            }

        } catch (Exception $e) {
            self::getDb()->rollback(); 
            throw new Exception("Ошибка сохранения заказа: " . htmlspecialchars($e->getMessage()));
        }
    }
    private static function saveOrderedItems($orderId, int $itemId, int $quantity) {
        $stmt = self::getDb()->prepare("CALL spSaveOrderedItems(?, ?, ?)");

        if ($stmt) {
            $stmt->bind_param("iii", $orderId, $itemId, $quantity);
            if (!$stmt->execute()) {
                throw new Exception("Ошибка сохранения позиций заказа: " . self::getDb()->error);
            }
            $stmt->close();
        } else {
            throw new Exception("Ошибка подготовки запроса: " . self::getDb()->error);
        }
    }
    public static function getOrders() {
        return new IteratorIterator(new ArrayIterator(self::fetchOrders()));
    }
    private static function fetchOrders() {
        $orders = [];
        if ($stmt = self::getDb()->prepare("CALL spGetOrders()")) {
            if ($stmt->execute()) {
                $result_set = $stmt->get_result();
                while ($row = $result_set->fetch_assoc()) {
                    if (!isset($orders[$row['id']])) { 
                        $orders[$row['id']] = new Order(
                            $row['customer'], 
                            $row['email'], 
                            $row['phone'], 
                            $row['address']
                        );
                        $orders[$row['id']]->setId($row['id']); 
                    }
                    $orders[$row['id']]->addItem($row['item_id'], $row['quantity']); 
                    $orders[$row['id']]->setCreated($row['created']); 
                }
                $stmt->close(); 
                return $orders; 
            } else { 
                throw new Exception("Ошибка получения заказов: " . self::getDb()->error); 
            } 
        } else { 
            throw new Exception("Ошибка подготовки запроса: " . self::getDb()->error); 
        } 
    }
    public static function userAdd(User $user) {
        $stmt = self::getDb()->prepare("CALL spSaveAdmin(?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sss", $user->getLogin(), $user->getPassword(), $user->getEmail());
            if (!$stmt->execute()) {
                throw new Exception("Ошибка добавления пользователя: " . self::getDb()->error);
            }
            $stmt->close();
        } else {
            throw new Exception("Ошибка подготовки запроса: " . self::getDb()->error);
        }
    }
    public static function userCheck(User $user): bool {
        if ($stmt = self::getDb()->prepare("CALL spGetAdmin(?)")) {
            $stmt->bind_param("s", $user->getLogin());
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                return ($result->num_rows > 0); 
            } else {
                throw new Exception("Ошибка проверки пользователя: " . self::getDb()->error);
            }
            $stmt->close();
        } else {
            throw new Exception("Ошибка подготовки запроса: " . self::getDb()->error);
        }
    }
    public static function userGet(User $user): User {
        if ($stmt = self::getDb()->prepare("CALL spGetAdmin(?)")) {
            $stmt->bind_param("s", $user->getLogin());

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $fetchedUser = new User($row['login'], $row['password'], $row['email']);
                    $fetchedUser->setId($row['id']);
                    return $fetchedUser;
                }
                throw new Exception("Такого пользователя не существует.");
            } else {
                throw new Exception("Ошибка при получении пользователя: " . self::getDb()->error);
            }
            $stmt->close();
        } else {
            throw new Exception("Ошибка подготовки запроса: " . self::getDb()->error);
        }
    }
    public static function createHash(string $password): string {
        return password_hash($password, PASSWORD_DEFAULT); 
    }
    public static function isAdmin(): bool {
        return isset($_SESSION['admin']); 
    }
    public static function logIn(User $user): bool {
        if (self::userCheck($user)) {
            $fetchedUser = self::userGet($user);
            if (password_verify($user->getPassword(), $fetchedUser->getPassword())) { 
                $_SESSION['admin'] = true; 
                return true; 
            }
            throw new Exception("Неверный пароль.");
        }
        throw new Exception("Такого пользователя не существует.");
    }
    public static function logOut() {
        unset($_SESSION['admin']); 
    }
}