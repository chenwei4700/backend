<?php
session_start();
if (!isset($_SESSION['account']) || $_SESSION['role'] !== 'm') {
    // 用户未登录或没有权限，跳转到登录页面
    header("Location: login.php?msg=請先登入或沒有權限");
    exit();
}

try {
    require_once 'db.php'; // 確保正確引入資料庫配置

    if (isset($_GET['postid'])) {
        $team_id = intval($_GET['postid']); // 確保 team_id 是整數

        // 查詢隊伍名稱和總分
        $sql = "
            SELECT 
                teams.team_name, 
                COALESCE(SUM(scores.score), 0) AS total_score
            FROM teams
            LEFT JOIN scores ON scores.team_id = teams.team_id
            WHERE teams.team_id = ?
            GROUP BY teams.team_id
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $team_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $team = $result->fetch_assoc();

        if (!$team) {
            throw new Exception('無效的隊伍 ID');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_team'])) {
            $team_name = trim($_POST['team_name']);
            $new_score = intval($_POST['new_score']);

            // 更新隊伍名稱
            $sql = "UPDATE teams SET team_name = ? WHERE team_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $team_name, $team_id);
            if (!$stmt->execute()) {
                throw new Exception('更新隊伍名稱失敗：' . $stmt->error);
            }

            // 刪除現有的總分記錄
            $sql = "DELETE FROM scores WHERE team_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $team_id);
            if (!$stmt->execute()) {
                throw new Exception('刪除現有總分記錄失敗：' . $stmt->error);
            }

            // 插入新的總分記錄
            $sql = "INSERT INTO scores (team_id, score) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $team_id, $new_score);
            if (!$stmt->execute()) {
                throw new Exception('插入新總分記錄失敗：' . $stmt->error);
            }

            // 成功更新後重新導向
            header("Location: score.php?msg=更新成功");
            exit();
        }
    } else {
        throw new Exception('無效的隊伍 ID');
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
    <title>更新隊伍資訊</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>更新隊伍資訊</h2>
    <form method="post">
        <div class="mb-3">
            <label for="teamName" class="form-label">隊伍名稱</label>
            <input
                type="text"
                class="form-control"
                id="teamName"
                name="team_name"
                value="<?= htmlspecialchars($team['team_name']) ?>"
                required
            >
        </div>
        <div class="mb-3">
            <label for="currentScore" class="form-label">當前總分</label>
            <input
                type="text"
                class="form-control"
                id="currentScore"
                name="current_score"
                value="<?= htmlspecialchars($team['total_score']) ?>"
                readonly
            >
        </div>
        <div class="mb-3">
            <label for="newScore" class="form-label">新總分</label>
            <input
                type="number"
                class="form-control"
                id="newScore"
                name="new_score"
                required
            >
        </div>
        <button type="submit" name="update_team" class="btn btn-success">更新</button>
        <a href="score.php" class="btn btn-secondary">返回</a>
    </form>
</div>
</body>
</html>