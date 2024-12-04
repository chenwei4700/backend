<?php
session_start();
if (!isset($_SESSION['account']) || $_SESSION['role'] !== 'M') {
    // 用户未登录或没有权限，跳转到登录页面
    header("Location: login.php?msg=請先登入或沒有權限");
    exit();
}

try {
    require_once 'db.php'; // 確保正確引入資料庫配置

    if (isset($_GET['postid'])) {
        $activity_id = intval($_GET['postid']); // 確保 activity_id 是整數

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

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_activity'])) {
            $activity_name = trim($_POST['activity_name']);
            $activity_description = trim($_POST['activity_description']);

            // 更新活動名稱和內容
            $sql = "UPDATE activities SET activity_name = ?, activity_description = ? WHERE activity_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $activity_name, $activity_description, $activity_id);
            if (!$stmt->execute()) {
                throw new Exception('更新活動失敗：' . $stmt->error);
            }

            // 成功更新後重新導向
            header("Location: activity_list.php?msg=更新成功");
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
    <title>更新活動</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>更新活動</h2>
    <form method="post">
        <div class="mb-3">
            <label for="activityName" class="form-label">活動名稱</label>
            <input
                type="text"
                class="form-control"
                id="activityName"
                name="activity_name"
                value="<?= htmlspecialchars($activity['activity_name']) ?>"
                required
            >
        </div>
        <div class="mb-3">
            <label for="activityDescription" class="form-label">活動內容</label>
            <textarea
                class="form-control"
                id="activityDescription"
                name="activity_description"
                rows="3"
                required
            ><?= htmlspecialchars($activity['activity_description']) ?></textarea>
        </div>
        <button type="submit" name="update_activity" class="btn btn-success">更新</button>
        <a href="activity_list.php" class="btn btn-secondary">返回</a>
    </form>
</div>
</body>
</html>