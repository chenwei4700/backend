

<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
$servername = "localhost"; // 主機名
$username = "root"; // 資料庫使用者
$password = ""; // 資料庫密碼
$dbname = "後端期末"; // 資料庫名稱

// 建立連接
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連接是否成功
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}
?>
