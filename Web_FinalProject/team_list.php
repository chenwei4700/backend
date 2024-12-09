<?php
session_start();
if (!isset($_SESSION['account'])) {
    // 用户未登录，跳转到登录页面
    header("Location: login.php?msg=請先登入");
    exit();
}

// 檢查是否為登出
if (isset($_POST["logout"])) {
    session_unset(); // 清除所有 session 變數
    session_destroy(); // 清除 session 資料
    header("Location: login.php"); // 重新導向登入
    exit();
}

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

    // 查詢隊伍名稱和組員名稱
    $sql = "
    SELECT 
        teams.team_id,
        teams.team_name, 
        GROUP_CONCAT(team_members.member_name SEPARATOR ', ') AS member_names,
        teams.team_image  -- 假設圖片欄位名為 team_image
    FROM teams
    LEFT JOIN team_members ON team_members.team_id = teams.team_id
    $condition
    GROUP BY teams.team_id
";

    // 執行查詢
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        throw new Exception('資料查詢失敗：' . mysqli_error($conn));
    }

    require_once "iheader.php"; // 確保 header.php 正確引入
?>
<div class="container mt-3">
    <div class="d-flex align-items-center justify-content-between">
        <!-- 搜尋欄 -->
        <form method="get" style="max-width: 400px; margin: auto;" class="input-group">
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
        </form>

        <!-- 新增隊伍按鈕 -->
        <div>
        <?php if ($_SESSION['role'] === 'm'): ?>
            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#addTeamModal">
                新增隊伍
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- 表格 -->
    <table class="table table-bordered table-striped mt-4" style="width: 900px; margin: auto;">
        <thead>
            <tr>
                <th>隊名</th>
                <th>組員</th>
                <th>小隊資訊</th>
            </tr>
        </thead>
        <tbody>
        <?php
// 顯示查詢結果
while ($row = mysqli_fetch_assoc($result)) {
    $team_id = htmlspecialchars($row["team_id"]);
    $team_name = htmlspecialchars($row["team_name"]);
    $member_names = htmlspecialchars($row["member_names"] ?? '-');
    $team_image = htmlspecialchars($row["team_image"]); // 圖片路徑
?>
    <tr>
        <td><?= $team_name ?></td>
        <td><?= $member_names ?></td>
        <td>
            <?php if ($_SESSION['role'] === 'm'): ?>
                <form action="delete_team.php?delete_id=<?= $team_id ?>" method="post" style="display:inline;">
                    <button type="submit" class="btn btn-danger btn-sm">刪除</button>
                </form>
                <a href="update_team.php?postid=<?= $team_id ?>" class="btn btn-warning btn-sm">修改</a>
            <?php endif; ?>
            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewTeamModal<?= $team_id ?>">檢視</button>
        </td>
    </tr>
    <!-- 檢視隊伍的 Modal -->
    <div class="modal fade" id="viewTeamModal<?= $team_id ?>" tabindex="-1" aria-labelledby="viewTeamModalLabel<?= $team_id ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewTeamModalLabel<?= $team_id ?>">隊伍資訊</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>隊伍名稱：</strong><?= $team_name ?></p>
                    <p><strong>組員：</strong><?= $member_names ?></p>
                    <p><strong>隊伍圖片：</strong></p>
                    <img src="<?= $team_image ?>" alt="<?= $team_name ?>" class="img-fluid">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">關閉</button>
                </div>
            </div>
        </div>
    </div>
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
            <form method="post" action="add_team.php" enctype="multipart/form-data">
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
                    <div class="mb-3">
                        <label for="teamImage" class="form-label">隊伍圖片</label>
                        <input
                            type="file"
                            class="form-control"
                            id="teamImage"
                            name="team_image"
                            accept="image/*"
                        >
                    </div>
                    <div id="memberFields">
                        <div class="mb-3 d-flex align-items-center">
                            <label for="memberName1" class="form-label">組員</label>
                            <br/>
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

<script src="./script.js"></script>