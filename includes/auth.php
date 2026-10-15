<?php
if(session_status() === PHP_SESSION_NONE){
    SESSION_start();
}
function require_staff() {
    if (empty($_SESSION['staff_id'])){
        header('Location:login.php');
        exit;
    }
}