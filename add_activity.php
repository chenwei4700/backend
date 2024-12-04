<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'db.php'; // 資料庫連線

    $activity_name = trim($_POST['activity_name']);
    $activity_description = trim($_POST['activity_description']);

    // 基本檢查
    if (empty($activity_name) || empty($activity_description)) {
        echo "<script>alert('活動名稱和內容不可為空！'); history.back();</script>";
        exit();
    }

    // 使用事务处理
    mysqli_begin_transaction($conn);

    try {
        // 插入活動名稱
        $sql = "INSERT INTO activities (activity_name, activity_description) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $activity_name, $activity_description);
        if (!$stmt->execute()) {
            throw new Exception('新增活動失敗：' . $stmt->error);
        }

        // 提交事务
        mysqli_commit($conn);

        echo "<script>alert('新增成功！'); window.location.href = 'activity_list.php';</script>";
    } catch (Exception $e) {
        // 出错则回滚事务
        mysqli_rollback($conn);
        error_log($e->getMessage(), 3, '/var/log/app_errors.log');
        echo "<script>alert('新增活動失敗，請稍後再試！'); history.back();</script>";
    } finally {
        $stmt->close();
        $conn->close();
    }
}
?>
