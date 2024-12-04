<?php
// 檢查是否為登出請求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    session_destroy(); // 清除 session 資料
    header("Location: login.php"); // 重新導向登入頁面
    exit();
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="utf-8">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>登出範例</title>
</head>

<body>

    <nav class="navbar navbar-expand-sm bg-light navbar-light border-bottom shadow-sm">
        <div class="container-fluid">
            <!-- 右側選單 -->
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="team_list.php">小隊列表</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="activity_list.php">活動</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="score.php">當前比分</a>
                </li>
            </ul>

            <!-- 登出按鈕 -->
            <form method="POST" class="d-inline">
                <button type="submit" name="logout" class="btn btn-danger">登出</button>
            </form>
        </div>
    </nav>

</body>

</html>
