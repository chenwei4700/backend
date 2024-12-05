<?php require_once "header.php"?>

<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 資料庫連線設定
$servername = "localhost";
$dbname = "test"; // 資料庫名稱
$dbUsername = "root"; // 資料庫使用者名稱
$dbPassword = ""; // 資料庫密碼

// 建立資料庫連線
$conn = mysqli_connect($servername, $dbUsername, $dbPassword, $dbname);

// 檢查連線是否成功
if (!$conn) {
    die("無法連線: " . mysqli_connect_error());
}

// 取得可能的錯誤訊息
$msg = $_GET["msg"] ?? "";

// 檢查是否有 POST 請求
if ($_POST) {
    // 取得使用者輸入的帳號和密碼
    $account = htmlspecialchars($_POST["account"] ?? "");
    $password = htmlspecialchars($_POST["password"] ?? "");

    // 從資料庫中查詢該帳號
    $stmt = $conn->prepare("SELECT * FROM user WHERE account = ?");
    $stmt->bind_param("s", $account);
    $stmt->execute();
    $result = $stmt->get_result();

    // 檢查是否找到對應的帳號
    if ($result->num_rows > 0) {
        // 取得使用者資料
        $row = $result->fetch_assoc();

        // 檢查密碼是否正確
        if ($row['password'] === $password) {
          // 如果密碼正確，保存帳號到 session 並跳轉到 score.php
          $_SESSION['account'] = $account;
          $_SESSION['role'] = $row['role'];

            header("Location: score.php");
            exit();
        } else {
            // 如果密碼錯誤，顯示錯誤訊息並跳轉回登入頁面
            header("Location: login.php?msg=帳號或密碼錯誤，請重試1");
            exit();
        }
    } else {
        // 如果帳號不存在，顯示錯誤訊息並跳轉回登入頁面
        header("Location: login.php?msg=帳號或密碼錯誤，請重試2");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>登入系統</title>
  <!-- 引入 Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h3 class="text-center">登入系統</h3>
        </div>
        <div class="card-body">
          <?php if ($msg): ?>
            <div class="alert alert-danger text-center">
              <?= htmlspecialchars($msg) ?>
            </div>
          <?php endif; ?>
          <form action="login.php" method="post">
            <div class="mb-3">
              <label for="account" class="form-label">帳號</label>
              <input type="text" name="account" id="account" class="form-control" required>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">密碼</label>
              <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary">登入</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- 引入 Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>