<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'db.php';

    // 檢查隊伍名稱是否存在
    $team_name = mysqli_real_escape_string($conn, $_POST['team_name']);
    $member_names = $_POST['member_names']; // 接收成員名陣列

    if (empty($team_name)) {
        echo "<script>alert('隊伍名稱不可為空！'); history.back();</script>";
        exit();
    }

    // 插入隊伍資料
    $insertTeamSql = "INSERT INTO teams (team_name) VALUES ('$team_name')";
    if (!mysqli_query($conn, $insertTeamSql)) {
        echo "<script>alert('隊伍新增失敗：" . mysqli_error($conn) . "'); history.back();</script>";
        exit();
    }

    $team_id = mysqli_insert_id($conn); // 獲取新隊伍的 ID

    // 插入組員資料
    if (!empty($member_names) && is_array($member_names)) {
        foreach ($member_names as $member_name) {
            $member_name = mysqli_real_escape_string($conn, $member_name);
            if (!empty(trim($member_name))) {
                $insertMemberSql = "INSERT INTO team_members (team_id, member_name) VALUES ('$team_id', '$member_name')";
                if (!mysqli_query($conn, $insertMemberSql)) {
                    echo "<script>alert('成員新增失敗：" . mysqli_error($conn) . "'); history.back();</script>";
                    exit();
                }
            }
        }
    }

    echo "<script>alert('新增成功！'); window.location.href = 'team_list.php';</script>";
    mysqli_close($conn);
}
?>

