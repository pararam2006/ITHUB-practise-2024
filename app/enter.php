<?php
require_once '../core/init.php'; 
try {
   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
       $login = Cleaner::str($_POST['login']);
       $password = Cleaner::str($_POST['password']);
       $user = new User($login, password_hash('', PASSWORD_DEFAULT)); 
       if (Eshop::logIn($user)) { 
           header('Location: /admin');
           exit(); 
       }
   }
} catch (Exception $e) { 
   echo 'Ошибка: ' . htmlspecialchars($e->getMessage()); 
}
?>
<h1>Вход на панель администрирования</h1>
<form action="login.php" method="post">
   <div>
       <label>Логин:</label>
       <input type="text" name="login" required>
   </div>
   <div>
       <label>Пароль:</label>
       <input type="password" name="password" required>
   </div>
   <div>
       <input type="submit" value="Войти">
   </div>
</form>
