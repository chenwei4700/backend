<?php
session_start();
if (!isset($_SESSION['account']) || $_SESSION['role'] !== 'm') {
    header("Location: login.php?msg=請先登入或沒有權限");
    exit();
}

try {
    require_once 'db.php';

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

        // 查詢與該活動相關的關卡
        $sql = "SELECT stage_name FROM stage WHERE activity_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $activity_id);
        $stmt->execute();
        $stages_result = $stmt->get_result();
        $stages = [];
        while ($row = $stages_result->fetch_assoc()) {
            $stages[] = $row['stage_name'];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
            // 刪除關聯的關卡
            $sql = "DELETE FROM stage WHERE activity_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $activity_id);
            if (!$stmt->execute()) {
                throw new Exception('刪除關卡失敗：' . $stmt->error);
            }

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
    echo '<div class="alert alert-danger">錯誤訊息：' . htmlspecialchars($e->getMessage()) . '</div>';
}

mysqli_close($conn);
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
    <p>你確定要刪除以下活動及其關卡嗎？</p>
    <table class="table table-bordered">
        <tr>
            <th>活動名稱</th>
            <td><?= htmlspecialchars($activity['activity_name']) ?></td>
        </tr>
        <tr>
            <th>活動內容</th>
            <td><?= htmlspecialchars($activity['activity_description']) ?></td>
        </tr>
        <tr>
            <th>關卡</th>
            <td>
                <?php if (!empty($stages)): ?>
                    <ul>
                        <?php foreach ($stages as $stage): ?>
                            <li><?= htmlspecialchars($stage) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    無關卡
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <form method="post">
        <button type="submit" name="confirm_delete" class="btn btn-danger">確認刪除</button>
        <a href="activity_list.php" class="btn btn-secondary">返回</a>
    </form>
</div>
</body>
</html>
