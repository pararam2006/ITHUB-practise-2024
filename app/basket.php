<?php
require_once 'core/init.php'; 
$items = Eshop::getItemsFromBasket();
$totalPrice = 0; 
?>
<p>Вернуться в <a href='/catalog'>каталог</a></p>
<h1>Ваша корзина</h1>
<table>
<tr>
	<th>N п/п</th>
	<th>Название</th>
	<th>Автор</th>
	<th>Год издания</th>
	<th>Цена, руб.</th>
	<th>Количество</th>
	<th>Удалить</th>
</tr>
<?php
if (!empty($items)) {
    foreach ($items as $itemId => $quantity) {
        $book = Eshop::getBookById($itemId); 
        if ($book) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($itemId) . "</td>";
            echo "<td>" . htmlspecialchars($book->getTitle()) . "</td>";
            echo "<td>" . htmlspecialchars($book->getAuthor()) . "</td>";
            echo "<td>" . htmlspecialchars($book->getPubyear()) . "</td>";
            echo "<td>" . htmlspecialchars(number_format($book->getPrice(), 2, '.', ' ')) . " руб.</td>";
            echo "<td>" . htmlspecialchars($quantity) . "</td>";
            echo "<td><button onclick=\"removeFromBasket('" . htmlspecialchars($itemId) . "')\">Удалить</button></td>"; // Кнопка для удаления из корзины
            echo "</tr>";
            $totalPrice += $book->getPrice() * $quantity;
        }
    }
} else {
    echo "<tr><td colspan='7'>Ваша корзина пуста.</td></tr>";
}
?>
</table>
<p>Всего товаров в корзине на сумму: <?php echo htmlspecialchars(number_format($totalPrice, 2, '.', ' ')); ?> руб.</p>
<div style="text-align:center">
	<input type="button" value="Оформить заказ!" onclick="location.href='/create_order'" />
</div>
<script>
function removeFromBasket(itemId) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "/remove_item_from_basket", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) { location.reload(); }
    };
    xhr.send("item_id=" + encodeURIComponent(itemId));
}
</script>
