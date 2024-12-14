<?php
session_start();
if (!isset($_SESSION['account']) || $_SESSION['role'] !== 'm') {
    header("Location: login.php?msg=請先登入或沒有權限");
    exit();
}

try {
    require_once 'db.php';

    if (isset($_GET['postid'])) {
        $activity_id = intval($_GET['postid']);

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

        // 查詢關卡資料
        $sql = "SELECT stage_id, stage_name FROM stage WHERE activity_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $activity_id);
        $stmt->execute();
        $stages_result = $stmt->get_result();
        $stages = $stages_result->fetch_all(MYSQLI_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_activity'])) {
            $activity_name = trim($_POST['activity_name']);
            $activity_description = trim($_POST['activity_description']);
            $stage_names = $_POST['stage_name'];

            // 更新活動名稱和內容
            $sql = "UPDATE activities SET activity_name = ?, activity_description = ? WHERE activity_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $activity_name, $activity_description, $activity_id);
            if (!$stmt->execute()) {
                throw new Exception('更新活動失敗：' . $stmt->error);
            }

            // 更新或新增關卡
            foreach ($stage_names as $stage_id => $stage_name) {
                if (!empty($stage_name)) {
                    if (is_numeric($stage_id)) {
                        // 更新已存在的關卡
                        $sql = "UPDATE stage SET stage_name = ? WHERE stage_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("si", $stage_name, $stage_id);
                        $stmt->execute();
                    } else {
                        // 新增新關卡
                        $sql = "INSERT INTO stage (activity_id, stage_name) VALUES (?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("is", $activity_id, $stage_name);
                        $stmt->execute();
                    }
                }
            }

            header("Location: activity_list.php?msg=更新成功");
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
        <h4>關卡內容</h4>
        <div id="stages-container">
            <?php foreach ($stages as $stage): ?>
                <div class="mb-3">
                    <label for="stage<?= $stage['stage_id'] ?>" class="form-label">關卡名稱</label>
                    <input
                        type="text"
                        class="form-control"
                        id="stage<?= $stage['stage_id'] ?>"
                        name="stage_name[<?= $stage['stage_id'] ?>]"
                        value="<?= htmlspecialchars($stage['stage_name']) ?>"
                    >
                </div>
            <?php endforeach; ?>
            <div class="mb-3">
                <label for="newStage" class="form-label">新增關卡</label>
                <input
                    type="text"
                    class="form-control"
                    id="newStage"
                    name="stage_name[new][]"
                    placeholder="輸入新的關卡名稱"
                >
            </div>
        </div>
        <button type="submit" name="update_activity" class="btn btn-success">更新</button>
        <a href="activity_list.php" class="btn btn-secondary">返回</a>
    </form>
</div>
</body>
</html>
