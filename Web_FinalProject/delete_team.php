<?php
session_start();
if (!isset($_SESSION['account']) || $_SESSION['role'] !== 'm') {
    // 用户未登录或没有权限，跳转到登录页面
    header("Location: login.php?msg=請先登入或沒有權限");
    exit();
}

try {
    require_once 'db.php'; // 確保這裡是正確的路徑並成功引入

    if (isset($_GET['delete_id'])) {
        $team_id = intval($_GET['delete_id']); // 確保 team_id 是整數

        // 查詢隊伍名稱和組員名稱
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

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
            // 刪除隊伍成員
            $sql = "DELETE FROM team_members WHERE team_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $team_id);
            if (!$stmt->execute()) {
                throw new Exception('刪除隊伍成員失敗：' . $stmt->error);
            }

            // 刪除隊伍
            $sql = "DELETE FROM teams WHERE team_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $team_id);
            if (!$stmt->execute()) {
                throw new Exception('刪除隊伍失敗：' . $stmt->error);
            }

            // 成功刪除後重新導向
            header("Location: team_list.php?msg=刪除成功");
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
    <title>確認刪除隊伍</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>確認刪除隊伍</h2>
    <p>你確定要刪除以下隊伍嗎？</p>
    <table class="table table-bordered">
        <tr>
            <th>隊名</th>
            <td><?= htmlspecialchars($team['team_name']) ?></td>
        </tr>
        <tr>
            <th>組員</th>
            <td><?= htmlspecialchars($team['member_names']) ?></td>
        </tr>
    </table>
    <form method="post">
        <button type="submit" name="confirm_delete" class="btn btn-danger">確認刪除</button>
        <a href="team_list.php" class="btn btn-secondary">返回</a>
    </form>
</div>
</body>
</html>