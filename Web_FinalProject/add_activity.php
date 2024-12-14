
<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'db.php'; // 資料庫連線
    try {
        // 新增活動到 activities 表
        $stmt = $conn->prepare("INSERT INTO activities (activity_name, activity_description) VALUES (?, ?)");
        $stmt->bind_param("ss", $_POST['activity_name'], $_POST['activity_description']);
        $stmt->execute();
        $activity_id = $stmt->insert_id;

        // 新增關卡到 stage 表
        $stages = [
            $_POST['sub_activity_1'],
            $_POST['sub_activity_2'],
            $_POST['sub_activity_3'],
            $_POST['sub_activity_4'],
            $_POST['sub_activity_5']
        ];

        $stage_stmt = $conn->prepare("INSERT INTO stage (activity_id, stage_name) VALUES (?, ?)");
        foreach ($stages as $stage) {
            if (!empty(trim($stage))) {
                $stage_stmt->bind_param("is", $activity_id, $stage);
                $stage_stmt->execute();
            }
        }

        // 完成後重定向
        header("Location: activity_list.php?msg=新增成功");
        exit();
    } catch (Exception $e) {
        error_log($e->getMessage(), 3, '/var/log/app_errors.log');
        echo '<div class="alert alert-danger">新增失敗，請稍後再試。</div>';
    }
}
?>
