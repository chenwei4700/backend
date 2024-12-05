<<?php
session_start();
if (!isset($_SESSION['account']) || $_SESSION['role'] !== 'm') {
    // 用户未登录或没有权限，跳转到登录页面
    header("Location: login.php?msg=請先登入或沒有權限");
    exit();
}

try {
    require_once 'db.php'; // 確保正確引入資料庫配置

    if (isset($_GET['delete_id'])) {
        $activity_id = intval($_GET['delete_id']); // 確保 activity_id 是整數

        // 查詢活動名稱和內容
        $sql = "SELECT activity_name, activity_description FROM activities WHERE activity_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $activity_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $activity = $result->fetch_assoc();

        if (!$activity) {
            throw new Exception('無效的活動 ID');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
            // 刪除活動
            $sql = "DELETE FROM activities WHERE activity_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $activity_id);
            if (!$stmt->execute()) {
                throw new Exception('刪除活動失敗：' . $stmt->error);
            }

            // 成功刪除後重新導向
            header("Location: activity_list.php?msg=刪除成功");
            exit();
        }
    } else {
        throw new Exception('無效的活動 ID');
    }
} catch (Exception $e) {
    // 捕捉例外並顯示錯誤訊息
    echo '<div class="alert alert-danger">錯誤訊息：' . htmlspecialchars($e->getMessage()) . '</div>';
}

mysqli_close($conn); // 關閉資料庫連接
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>確認刪除活動</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>確認刪除活動</h2>
    <p>你確定要刪除以下活動嗎？</p>
    <table class="table table-bordered">
        <tr>
            <th>活動名稱</th>
            <td><?= htmlspecialchars($activity['activity_name']) ?></td>
        </tr>
        <tr>
            <th>活動內容</th>
            <td><?= htmlspecialchars($activity['activity_description']) ?></td>
        </tr>
    </table>
    <form method="post">
        <button type="submit" name="confirm_delete" class="btn btn-danger">確認刪除</button>
        <a href="activity_list.php" class="btn btn-secondary">返回</a>
    </form>
</div>
</body>
</html>