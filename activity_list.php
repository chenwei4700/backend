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
        $condition = " WHERE activities.activity_name LIKE '%$searchtxt%'";
    }

    // 查詢活動名稱和內容
    $sql = "
        SELECT activity_name, activity_description
        FROM activities
        $condition
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
        <div>
            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addTeamModal">
                新增隊伍
            </button>
        </div>
    </div>


    <!-- 表格顯示 -->
    <table class="table table-bordered table-striped mt-4" style="width: 900px; margin: auto;">
        <thead>
            <tr>
                <th>活動</th>
                <th style="text-align: center;">活動內容</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // 顯示查詢結果
            while ($row = mysqli_fetch_assoc($result)) {
                $activity_name = htmlspecialchars($row["activity_name"]);
                $activity_description = htmlspecialchars($row["activity_description"]);
            ?>
                <tr>
                    <td><?= $activity_name ?></td>
                    <td><?= $activity_description ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- 新增隊伍的 Modal -->
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
