<?php
function checkLogin() {
    session_start();
    if (!isset($_SESSION['account'])) {
        header("Location: login.php?msg=請先登入");
        exit();
    }
}
?>
