
<?php
session_start();

// 檢查用戶是否已登入
if (!isset($_SESSION['account'])) {
    header("Location: login.php?msg=請先登入");
    exit();
}

try {
    require_once 'db.php'; // 確保正確引入資料庫配置

    // 預設搜尋條件
    $searchtxt = isset($_GET['searchtxt']) ? trim($_GET['searchtxt']) : '';

    $sql = "
    SELECT 
        a.activity_id,
        a.activity_name,
        a.activity_description,
        GROUP_CONCAT(s.stage_name ORDER BY s.stage_id SEPARATOR ', ') AS stages
    FROM 
        activities a
    LEFT JOIN 
        stage s ON a.activity_id = s.activity_id
";
$params = [];
if (!empty($searchtxt)) {
    $sql .= " WHERE a.activity_name LIKE ?";
    $params[] = "%" . $searchtxt . "%";
}
$sql .= " GROUP BY a.activity_id";


    // 預處理 SQL 查詢
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    require_once "iheader.php"; // 正確引入頁面標頭
?>
<div class="container mt-3">
    <!-- 搜索表单 -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <form method="get" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="d-flex">
            <input
                type="text"
                class="form-control me-2"
                placeholder="請輸入活動名稱"
                name="searchtxt"
                value="<?= htmlspecialchars($searchtxt) ?>"
                required
            >
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-search"></i> 搜尋
            </button>
        </form>
        <?php if ($_SESSION['role'] === 'm'): ?>
        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addActivityModal">新增關卡</button>
        <?php endif; ?>
    </div>

    <!-- 活动表格 -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>活動名稱</th>
                    <th style="text-align: center;">活動內容</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
    <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= htmlspecialchars($row['activity_name']) ?></td>
            <td><?= htmlspecialchars($row['activity_description']) ?></td>
            
            <td>
                <?php if ($_SESSION['role'] === 'm'): ?>
                    <form action="delete_activity.php?delete_id=<?= htmlspecialchars($row['activity_id']) ?>" method="post" style="display:inline;">
                        <button type="submit" class="btn btn-danger btn-sm">刪除</button>
                    </form>
                    <a href="update_activity.php?postid=<?= htmlspecialchars($row['activity_id']) ?>" class="btn btn-warning btn-sm">修改</a>
                <?php endif; ?>
                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewActivityModal<?= htmlspecialchars($row['activity_id']) ?>">檢視</button>
            </td>
        </tr>
        <!-- 動態生成的模態視窗 -->
        <div class="modal fade" id="viewActivityModal<?= htmlspecialchars($row['activity_id']) ?>" tabindex="-1" aria-labelledby="viewActivityModalLabel<?= htmlspecialchars($row['activity_id']) ?>" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewActivityModalLabel<?= htmlspecialchars($row['activity_id']) ?>">活動詳細資料</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <b><h4>活動名稱:</h4></b>
                        <p><b><?= htmlspecialchars(string: $row['activity_name']) ?></b></p>
                        <b><h4>活動敘述:</h4></b>
                        <p><b><?= htmlspecialchars($row['activity_description']) ?></b></p>
                        <b><h4>關卡列表:</h4></b>
                        <ul>
                            <?php
                            $stages = explode(', ', $row['stages']);
                            foreach ($stages as $index => $stage) {
                                echo "<li>關卡 " . ($index + 1) . ": " . htmlspecialchars($stage) . "</li>";
                            }
                            ?>
                        </ul>
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
</div>

<!-- 新增活動模态框 -->
<div class="modal fade" id="addActivityModal" tabindex="-1" aria-labelledby="addActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addActivityModalLabel">新增關卡</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="add_activity.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="activityName" class="form-label">活動名稱</label>
                        <input
                            type="text"
                            class="form-control"
                            id="activityName"
                            name="activity_name"
                            placeholder="請輸入活動名稱"
                            required
                        >
                    </div>
                    <div class="mb-3">
                        <label for="activityDescription" class="form-label">活動內容</label>
                        <textarea
                            class="form-control"
                            id="activityDescription"
                            name="activity_description"
                            placeholder="請輸入活動內容"
                            rows="3"
                            required
                        ></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="subActivity1" class="form-label">關卡 1</label>
                        <input
                            type="text"
                            class="form-control"
                            id="subActivity1"
                            name="sub_activity_1"
                            placeholder="請輸入關卡 1"
                        >
                    </div>
                    <div class="mb-3">
                        <label for="subActivity2" class="form-label">關卡 2</label>
                        <input
                            type="text"
                            class="form-control"
                            id="subActivity2"
                            name="sub_activity_2"
                            placeholder="請輸入關卡 2"
                        >
                    </div>
                    <div class="mb-3">
                        <label for="subActivity3" class="form-label">關卡 3</label>
                        <input
                            type="text"
                            class="form-control"
                            id="subActivity3"
                            name="sub_activity_3"
                            placeholder="請輸入關卡 3"
                        >
                    </div>
                    <div class="mb-3">
                        <label for="subActivity4" class="form-label">關卡 4</label>
                        <input
                            type="text"
                            class="form-control"
                            id="subActivity4"
                            name="sub_activity_4"
                            placeholder="請輸入關卡 4"
                        >
                    </div>
                    <div class="mb-3">
                        <label for="subActivity5" class="form-label">關卡 5</label>
                        <input
                            type="text"
                            class="form-control"
                            id="subActivity5"
                            name="sub_activity_5"
                            placeholder="請輸入關卡 5"
                        >
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-success">新增</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
    // 關閉連接
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    error_log($e->getMessage(), 3, '/var/log/app_errors.log');
    echo '<div class="alert alert-danger">操作失敗，請稍後再試。</div>';
}

require_once "footer.php"; // 正確引入頁尾
?>

