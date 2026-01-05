<?php
// app/Interfaces/UserServiceInterface.php

interface UserServiceInterface {
    public function login($email, $password, $code2fa = null);
    public function logout();
    public function isAuthenticated();
    public function getUserType();
}
?>