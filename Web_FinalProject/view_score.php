<?php
session_start();
if (!isset($_SESSION['account'])) {
    header("Location: login.php?msg=請先登入");
    exit();
}

try {
    require_once 'db.php';

    if (!isset($_GET['team_id']) || empty($_GET['team_id'])) {
        throw new Exception("缺少必要的隊伍 ID 參數");
    }

    $team_id = intval($_GET['team_id']);

    // 查詢隊伍名稱和總分
    $team_sql = "
        SELECT team_name, COALESCE(SUM(scores.score), 0) AS total_score
        FROM teams
        LEFT JOIN scores ON scores.team_id = teams.team_id
        WHERE teams.team_id = ?
        GROUP BY teams.team_id
    ";
    $team_stmt = $conn->prepare($team_sql);
    $team_stmt->bind_param("i", $team_id);
    $team_stmt->execute();
    $team_result = $team_stmt->get_result();

    if ($team_result->num_rows === 0) {
        throw new Exception("找不到該隊伍");
    }

    $team_data = $team_result->fetch_assoc();

    // 查詢每個活動的總分
    $activity_scores_sql = "
        SELECT 
            activities.activity_id, 
            activities.activity_name, 
            COALESCE(SUM(scores.score), 0) AS total_score
        FROM activities
        LEFT JOIN stage ON stage.activity_id = activities.activity_id
        LEFT JOIN scores ON scores.stage_id = stage.stage_id AND scores.team_id = ?
        GROUP BY activities.activity_id
        ORDER BY activities.activity_id
    ";
    $activity_stmt = $conn->prepare($activity_scores_sql);
    $activity_stmt->bind_param("i", $team_id);
    $activity_stmt->execute();
    $activity_result = $activity_stmt->get_result();

    require_once "iheader.php";
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>隊伍詳情</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">隊伍詳情</h2>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">隊伍名稱: <?= htmlspecialchars($team_data['team_name']) ?></h5>
            <p class="card-text">總分: <?= htmlspecialchars($team_data['total_score']) ?></p>
        </div>
    </div>
    <table class="table table-bordered table-striped text-center mt-4">
        <thead>
            <tr>
                <th>活動名稱</th>
                <th>總分</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($activity_row = $activity_result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($activity_row['activity_name']) ?></td>
        <td><?= htmlspecialchars($activity_row['total_score']) ?></td>
        <td>
        <?php if ($_SESSION['role'] === 'm'): ?>
            <a href="update_score.php?postid=<?= htmlspecialchars($team_id) ?>&activity_id=<?= htmlspecialchars($activity_row['activity_id']) ?>" 
            class="btn btn-warning btn-sm">修改</a>
<?php endif; ?>
<button type="button" 
        class="btn btn-info btn-sm" 
        data-bs-toggle="modal" 
        data-bs-target="#viewActivityModal<?= htmlspecialchars($activity_row['activity_id']) ?>">檢視</button>

        </td>
    </tr>
    
    <!-- Modal -->
<!-- Modal -->
<div class="modal fade" id="viewActivityModal<?= htmlspecialchars($activity_row['activity_id']) ?>" tabindex="-1" aria-labelledby="viewActivityModalLabel<?= htmlspecialchars($activity_row['activity_id']) ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewActivityModalLabel<?= htmlspecialchars($activity_row['activity_id']) ?>">活動：<?= htmlspecialchars($activity_row['activity_name']) ?> 詳情</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div>
                    <?php
                    // 查詢活動關卡的分數
                    $stage_scores_sql = "
                        SELECT stage.stage_name, COALESCE(scores.score, 0) AS score
                        FROM stage
                        LEFT JOIN scores ON scores.stage_id = stage.stage_id AND scores.team_id = ?
                        WHERE stage.activity_id = ?
                        ORDER BY stage.stage_id
                    ";
                    $stage_stmt = $conn->prepare($stage_scores_sql);
                    $stage_stmt->bind_param("ii", $team_id, $activity_row['activity_id']);
                    $stage_stmt->execute();
                    $stage_result = $stage_stmt->get_result();
                    ?>

                    <!-- 使用 <p> 標籤呈現數據 -->
                    <?php while ($stage_row = $stage_result->fetch_assoc()): ?>
                        <p>
                            <strong>關卡名稱：</strong> <?= htmlspecialchars($stage_row['stage_name']) ?><br>
                            <strong>分數：</strong> <?= htmlspecialchars($stage_row['score']) ?>
                        </p>
                        <hr> <!-- 分隔每個關卡 -->
                    <?php endwhile; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
            </div>
        </div>
    </div>
</div>


    <?php endwhile; ?>
        </tbody>
    </table>
    <div class="text-center">
        <a href="score.php" class="btn btn-primary">返回</a>
    </div>
</div>
<?php require_once 'footer.php'; ?>
<?php
    $team_stmt->close();
    $activity_stmt->close();
    mysqli_close($conn);
} catch (Exception $e) {
    echo '<div class="alert alert-danger text-center">錯誤訊息：' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>