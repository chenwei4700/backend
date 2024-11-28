<?php
session_start();

try {
    require_once 'db.php'; // 確保這裡是正確的路徑並成功引入

    // 預設搜尋條件
    $searchtxt = isset($_GET['searchtxt']) ? trim($_GET['searchtxt']) : '';

    // 建立 SQL 查詢條件
    $condition = '';
    if (!empty($searchtxt)) {
        // 避免 SQL 注入
        $searchtxt = mysqli_real_escape_string($conn, $searchtxt);
        $condition = " WHERE teams.team_name LIKE '%$searchtxt%'";
    }

    // 查詢隊伍名稱和總分
    $sql = "
        SELECT teams.team_name, COALESCE(SUM(scores.score), 0) AS total_score
        FROM teams
        LEFT JOIN scores ON scores.team_id = teams.team_id
        $condition
        GROUP BY teams.team_id
    ";

    // 執行查詢
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        throw new Exception('資料查詢失敗：' . mysqli_error($conn));
    }

    require_once "header.php"; // 確保 header.php 正確引入
?>
<div class="container mt-3">
    <div class="d-flex align-items-center justify-content-between">
        <!-- 搜尋欄 -->
        <div class="input-group" style="max-width: 400px; margin: auto;">
            <input 
                type="text" 
                class="form-control" 
                placeholder="請輸入隊伍名稱" 
                name="searchtxt"
                value="<?= htmlspecialchars($searchtxt) ?>"
            >
            <button class="btn btn-outline-primary" type="submit">
                <i class="fas fa-search"></i>
            </button>
        </div>

        <!-- 新增隊伍按鈕 -->


    </div>

    <!-- 表格 -->
    <table class="table table-bordered table-striped mt-4" style="width: 900px; margin: auto;">
        <thead>
            <tr>
                <th>隊名</th>
                <th>總分</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // 顯示查詢結果
            while ($row = mysqli_fetch_assoc($result)) {
                $team_name = htmlspecialchars($row["team_name"]);
                $total_score = htmlspecialchars($row["total_score"]);

                // 如果總分為 0，顯示 -
                $total_score_display = ($total_score == 0) ? '-' : $total_score;
            ?>
                <tr>
                    <td><?= $team_name ?></td>
                    <td><?= $total_score_display ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>


<div class="modal fade" id="addTeamModal" tabindex="-1" aria-labelledby="addTeamModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTeamModalLabel">新增隊伍</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="add_team.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="teamName" class="form-label">隊伍名稱</label>
                        <input
                            type="text"
                            class="form-control"
                            id="teamName"
                            name="team_name"
                            placeholder="請輸入隊伍名稱"
                            required
                        >
                    </div>
                    <div id="memberFields">
                        <div class="mb-3 d-flex align-items-center">
                            <label for="memberName1" class="form-label">組員姓名</label>
                            <input
                                type="text"
                                class="form-control ms-2"
                                id="memberName1"
                                name="member_names[]"
                                placeholder="請輸入組員姓名"
                                required
                            >
                            <button type="button" class="btn btn-outline-primary ms-2 add-member">
                                +
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-outline-success">新增</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
    // 關閉資料庫連接
    mysqli_close($conn); // 使用 mysqli_close 而不是設為 null
} catch (Exception $e) {
    // 捕捉例外並顯示錯誤訊息
    echo '<div class="alert alert-danger">錯誤訊息：' . htmlspecialchars($e->getMessage()) . '</div>';
}

require_once "footer.php"; // 確保 footer.php 正確引入
?>