<?php
session_start();
if (!isset($_SESSION['account']) || $_SESSION['role'] !== 'm') {
    header("Location: login.php?msg=請先登入或沒有權限");
    exit();
}

try {
    require_once 'db.php';

    if (!isset($_GET['postid']) || !isset($_GET['activity_id'])) {
        throw new Exception('無效的隊伍或活動 ID');
    }

    $team_id = intval($_GET['postid']);
    $activity_id = intval($_GET['activity_id']);

    // 查詢隊伍分數
    $sql = "
        SELECT stage.stage_id, stage.stage_name, COALESCE(scores.score, 0) AS score
        FROM stage
        LEFT JOIN scores ON scores.stage_id = stage.stage_id AND scores.team_id = ?
        WHERE stage.activity_id = ?
        ORDER BY stage.stage_id
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $team_id, $activity_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $current_scores = [];
    while ($row = $result->fetch_assoc()) {
        $current_scores[$row['stage_id']] = $row['score'];
    }

    if (isset($_POST['update_scores'])) {
        $scores = $_POST['scores'];

        foreach ($scores as $stage_id => $score) {
            $stage_id = intval($stage_id);
            $score = intval($score);

            $update_sql = "
                UPDATE scores
                SET score = ?
                WHERE team_id = ? AND stage_id = ?
            ";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("iii", $score, $team_id, $stage_id);
            $update_stmt->execute();
        }

        header("Location: score.php?msg=分數更新成功");
        exit();
    }
} catch (Exception $e) {
    echo '<div class="alert alert-danger">錯誤訊息：' . htmlspecialchars($e->getMessage()) . '</div>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修改關卡分數</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>修改關卡分數</h2>
    <form method="post" action="update_score.php?postid=<?= $team_id ?>&activity_id=<?= $activity_id ?>">
        <?php if (!empty($current_scores)): ?>
            <?php foreach ($current_scores as $stage_id => $score): ?>
                <div class="mb-3">
                    <label for="score_<?= htmlspecialchars($stage_id) ?>" class="form-label">
                        <?= htmlspecialchars($stage_id) ?>（目前分數：<?= htmlspecialchars($score) ?>）
                    </label>
                    <input
                        type="number"
                        class="form-control"
                        id="score_<?= htmlspecialchars($stage_id) ?>"
                        name="scores[<?= htmlspecialchars($stage_id) ?>]"
                        value="<?= htmlspecialchars($score) ?>"
                        required
                    >
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-danger">沒有找到相關的關卡分數數據。</p>
        <?php endif; ?>
        <button type="submit" name="update_scores" class="btn btn-success">更新分數</button>
        <a href="score.php" class="btn btn-secondary">返回</a>
    </form>
</div>
</body>
</html>