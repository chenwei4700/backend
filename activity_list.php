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

    // 建立查詢條件
    $sql = "SELECT activity_id, activity_name, activity_description FROM activities";
    $params = [];
    if (!empty($searchtxt)) {
        $sql .= " WHERE activity_name LIKE ?";
        $params[] = "%" . $searchtxt . "%";
    }

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
        <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addActivityModal">新增關卡</button>
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
                            <?php if ($_SESSION['role'] === 'M'): ?>
                                <form action="delete_activity.php?delete_id=<?= htmlspecialchars($row['activity_id']) ?>" method="post" style="display:inline;">
                                    <button type="submit" class="btn btn-danger btn-sm">刪除</button>
                                </form>
                                <a href="update_activity.php?postid=<?= htmlspecialchars($row['activity_id']) ?>" class="btn btn-warning btn-sm">修改</a>
                            <?php endif; ?>
                        </td>
                    </tr>
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