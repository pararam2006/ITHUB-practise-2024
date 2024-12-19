CREATE DATABASE eshop;
CREATE TABLE `catalog`(
    `id` INT AUTO_INCREMENT,
    `title` VARCHAR(255),
    `author` VARCHAR(255),
    `price` INT,
    `pubyear` INT,
    PRIMARY KEY (`id`)
);
CREATE TABLE `orders`(
    `id` INT AUTO_INCREMENT,
    `order_id` VARCHAR(50) UNIQUE,
    `customer` VARCHAR(50),
    `email` VARCHAR(50),
    `phone` VARCHAR(50),
    `address` VARCHAR(255),
    `datetime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
);
CREATE TABLE `ordered_items`(
    `id` INT AUTO_INCREMENT,
    `order_id` VARCHAR(50),
    `item_id` INT,
    `quantity` INT,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`item_id`) REFERENCES `catalog`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON DELETE RESTRICT ON UPDATE CASCADE
);
CREATE TABLE `admins`(
    `id` INT AUTO_INCREMENT,
    `login` VARCHAR(255),
    `password` VARCHAR(255),
    `email` VARCHAR(50),
    `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
);
CREATE PROCEDURE spAddItemToCatalog(
    IN title VARCHAR(255),
    IN author VARCHAR(255),
    IN price INT,
    IN pubyear INT
)
BEGIN
    INSERT INTO `catalog`(`title`, `author`, `price`, `pubyear`) VALUES(title, author, price, pubyear);
END

CREATE PROCEDURE spGetItemsFromCatalog()
BEGIN
    SELECT `id`, `title`, `author`, `price`, `pubyear`
        FROM `catalog`;
END

CREATE PROCEDURE spGetItemsForBasket(IN ids VARCHAR(255))
BEGIN
    SELECT `id`, `title`, `author`, `price`, `pubyear` FROM `catalog` WHERE FIND_IN_SET(`id`, ids);
END

CREATE PROCEDURE spSaveOrder(
    IN order_id VARCHAR(50),
    IN customer VARCHAR(50),
    IN email VARCHAR(50),
    IN phone VARCHAR(50),
    IN address VARCHAR(255)
)
BEGIN
    INSERT INTO `orders`(`order_id`, `customer`, `email`, `phone`, `address`) VALUES(order_id, customer, email, phone, address);
END

CREATE PROCEDURE spSaveOrderedItems(
    IN order_id VARCHAR(50),
    IN item_id INT,
    IN quantity INT
)
BEGIN
    INSERT INTO `ordered_items`(`order_id`, `item_id`, `quantity`)
        VALUES(order_id, item_id, quantity);
END

CREATE PROCEDURE spGetOrders()
BEGIN
    SELECT `order_id` as `id`, `customer`, `email`, `phone`, `address`, UNIX_TIMESTAMP(`datetime`) as `date` FROM `orders`;
END

CREATE PROCEDURE spGetOrderedItems(IN order_id VARCHAR(255))
BEGIN
    SELECT `title`, `author`, `price`, `pubyear`, `quantity`
        FROM `catalog`
        INNER JOIN `ordered_items`
        ON `catalog`.`id` = `ordered_items`.`item_id`
        WHERE `ordered_items`.`order_id` = order_id;
END

CREATE PROCEDURE spSaveAdmin(
    IN u_login VARCHAR(255),
    IN u_password VARCHAR(255),
    IN u_email VARCHAR(255)
)
BEGIN
    INSERT INTO `admins`(`login`, `password`, `email`)
        VALUES(u_login, u_password, u_email);
END

CREATE PROCEDURE spGetAdmin(
    IN u_login VARCHAR(255)
)
BEGIN
    SELECT `id`, `login`, `password` as `hash`, `email` FROM `admins` WHERE `login` = u_login;
END

