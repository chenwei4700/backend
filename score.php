<?php
session_start();
if (!isset($_SESSION['account'])) {
    header("Location: login.php?msg=請先登入");
    exit();
}

try {
    require_once 'db.php';

    $searchtxt = isset($_GET['searchtxt']) ? trim($_GET['searchtxt']) : '';

    $condition = '';
    if (!empty($searchtxt)) {
        $searchtxt = mysqli_real_escape_string($conn, $searchtxt);
        $condition = " WHERE teams.team_name LIKE '%$searchtxt%'";
    }

    $sql = "
        SELECT teams.team_id, teams.team_name, COALESCE(SUM(scores.score), 0) AS total_score
        FROM teams
        LEFT JOIN scores ON scores.team_id = teams.team_id
        $condition
        GROUP BY teams.team_id
        ORDER BY total_score DESC, teams.team_name ASC
    ";

    $result = mysqli_query($conn, $sql);
    if (!$result) {
        throw new Exception('資料查詢失敗：' . mysqli_error($conn));
    }

    require_once "iheader.php";
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>當前比分</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
            max-width: 800px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .table {
            margin-top: 20px;
        }
        .table th {
            background-color: #007bff;
            color: #fff;
            text-align: center;
        }
        .table tr:hover {
            background-color: #f1f1f1;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .form-control {
            border-radius: 20px;
        }
        .search-container {
            display: flex;
            justify-content: center;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center mb-4">當前比分</h2>
    <form method="get" action="score.php" class="search-container">
        <div class="input-group" style="max-width: 400px;">
            <input
                type="text"
                class="form-control"
                placeholder="請輸入隊伍名稱"
                name="searchtxt"
                value="<?= htmlspecialchars($searchtxt) ?>"
            >
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-search"></i> 搜尋
            </button>
        </div>
    </form>

    <table class="table table-bordered table-striped text-center">
        <thead>
            <tr>
                <th>隊名</th>
                <th>總分</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = mysqli_fetch_assoc($result)) {
                $team_id = htmlspecialchars($row["team_id"]);
                $team_name = htmlspecialchars($row["team_name"]);
                $total_score = htmlspecialchars($row["total_score"]);
                $total_score_display = ($total_score == 0) ? '-' : $total_score;
            ?>
                <tr>
                    <td><?= $team_name ?></td>
                    <td><?= $total_score_display ?></td>
                    <td>
                        <?php if ($_SESSION['role'] === 'm'): ?>
                            <a href="update_score.php?postid=<?= $team_id ?>" class="btn btn-warning btn-sm">修改</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
    mysqli_close($conn);
} catch (Exception $e) {
    echo '<div class="alert alert-danger text-center">錯誤訊息：' . htmlspecialchars($e->getMessage()) . '</div>';
}

require_once "footer.php";
?>