<?php
session_start();
if (isset($_SESSION["account"])) {
    // 如果使用者已登入，避免重複登入
    header("Location: score.php");
    exit();
}

$msg = $_GET["msg"] ?? "";

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $account = $_POST["account"] ?? "";
    $password = $_POST["password"] ?? "";

    try {
        // 準備查詢語句，查詢帳號和角色
        $stmt = $conn->prepare("SELECT password, role FROM account WHERE account = ?");
        if (!$stmt) {
            throw new Exception("SQL 錯誤: " . $conn->error);
        }

        $stmt->bind_param("s", $account);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($db_password, $role);
            $stmt->fetch();

            // 直接比對明文密碼
            if ($password == $db_password) {
                // 登入成功，設置 session
                $_SESSION["account"] = $account;
                $_SESSION["role"] = $role;

                header("Location: score.php");
                exit();
            }
        }

        // 錯誤處理
        header("Location: login.php?msg=帳號或密碼輸入錯誤");
        exit();
    } catch (Exception $e) {
        echo '錯誤訊息: ' . $e->getMessage();
    }
}

// 關閉連接
$conn->close();
?>


<?php require_once "header.php" ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-light text-dark">
                    <h3 class="card-title">登入</h3>
                </div>
                <div class="card-body">
                    <!-- 登入表單 -->
                    <form action="login.php" method="post">
                        <div class="mb-3">
                            <label for="account" class="form-label">帳號</label>
                            <input type="text" class="form-control" id="account" name="account" placeholder="請輸入帳號">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">密碼</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="請輸入密碼">
                        </div>
                        <div class="mt-3 text-danger">
                            <?= $msg ?>
                        </div><br>
                        <div class="d-flex">
                        <button type="submit" class="btn btn-outline-primary ms-2 w-50">登入</button>
                            <a href="register.php" class="btn btn-outline-danger ms-2 w-50">註冊</a>
                        </div>


                        <!-- 顯示訊息 -->

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "footer.php" ?>