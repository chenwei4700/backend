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

        // 查詢隊伍名稱和成員
        $sql = "
            SELECT 
                teams.team_name, 
                GROUP_CONCAT(team_members.member_name SEPARATOR ', ') AS member_names
            FROM teams
            LEFT JOIN team_members ON team_members.team_id = teams.team_id
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
            $member_names = array_map('trim', explode(',', $_POST['member_names']));

            // 更新隊伍名稱
            $sql = "UPDATE teams SET team_name = ? WHERE team_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $team_name, $team_id);
            if (!$stmt->execute()) {
                throw new Exception('更新隊伍名稱失敗：' . $stmt->error);
            }

            // 刪除現有的隊伍成員
            $sql = "DELETE FROM team_members WHERE team_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $team_id);
            if (!$stmt->execute()) {
                throw new Exception('刪除現有隊伍成員失敗：' . $stmt->error);
            }

            // 插入新的隊伍成員
            $sql = "INSERT INTO team_members (team_id, member_name) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            foreach ($member_names as $member_name) {
                $stmt->bind_param("is", $team_id, $member_name);
                if (!$stmt->execute()) {
                    throw new Exception('插入隊伍成員失敗：' . $stmt->error);
                }
            }

            // 成功更新後重新導向
            header("Location: team_list.php?msg=更新成功");
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
    <title>更新隊伍</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>更新隊伍</h2>
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
            <label for="memberNames" class="form-label">組員 (逗號分隔)</label>
            <textarea
                class="form-control"
                id="memberNames"
                name="member_names"
                rows="3"
                required
            ><?= htmlspecialchars($team['member_names']) ?></textarea>
        </div>
        <button type="submit" name="update_team" class="btn btn-success">更新</button>
        <a href="team_list.php" class="btn btn-secondary">返回</a>
    </form>
</div>
</body>
</html>