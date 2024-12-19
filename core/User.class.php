<?php
class User {
    private $id;
    private $login;
    private $password; 
    private $email;
    public function __construct($login, $password, $email) {
        $this->login = $login;
        $this->password = $password; 
        $this->email = $email;
    }
    public function getId() {
        return $this->id;
    }
    public function setId($id) {
        $this->id = $id;
    }
    public function getLogin() {
        return $this->login;
    }
    public function getPassword() {
        return $this->password;
    }
    public function getEmail() {
        return $this->email;
    }
}
