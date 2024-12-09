<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
$servername = "sql101.byethost7.com"; // 主機名
$username = "b7_37869105"; // 資料庫使用者
$password = "35658142"; // 資料庫密碼
$dbname = "b7_37869105_ex"; // 資料庫名稱

// 建立連接
$conn = new mysqli($servername, $username, $password, $dbname);

$conn->set_charset("utf8mb4");

// 檢查連接是否成功
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

?>