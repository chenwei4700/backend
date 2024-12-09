-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2024-12-08 17:32:00
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `test`
--

-- --------------------------------------------------------

--
-- 資料表結構 `activities`
--

CREATE TABLE `activities` (
  `activity_id` int(11) NOT NULL,
  `activity_name` char(20) DEFAULT NULL,
  `activity_description` varchar(200) DEFAULT NULL,
  `sub_activity_1` varchar(255) DEFAULT NULL,
  `sub_activity_2` varchar(255) DEFAULT NULL,
  `sub_activity_3` varchar(255) DEFAULT NULL,
  `sub_activity_4` varchar(255) DEFAULT NULL,
  `sub_activity_5` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `activities`
--

INSERT INTO `activities` (`activity_id`, `activity_name`, `activity_description`, `sub_activity_1`, `sub_activity_2`, `sub_activity_3`, `sub_activity_4`, `sub_activity_5`) VALUES
(1, '水球接力賽', '大學生宿營中的水球接力賽，每隊需將水球傳遞至終點，途中需完成各種挑戰，依照時間和表現計分', '水球運送', '水球跳躍', '水球投籃', '水球傳遞', '水球破壞王\r\n'),
(2, '障礙賽跑', '設有多重障礙的賽道，比賽隊伍必須越過不同的障礙，並且每一關卡會依照完成速度給予分數', NULL, NULL, NULL, NULL, NULL),
(3, '水槍對抗賽', '兩隊分成對抗方，每隊需用水槍擊中對方，分數依照準確度和擊中次數來決定', NULL, NULL, NULL, NULL, NULL),
(4, '捉迷藏挑戰', '隊伍需在限定區域內找到指定物品或人物，每找到一個物品即可獲得計分，最後依照完成數量計分', NULL, NULL, NULL, NULL, NULL),
(5, '投擲飛盤', '每隊需將飛盤投擲到指定目標，並依照飛盤命中準確度和距離給予分數', NULL, NULL, NULL, NULL, NULL),
(6, '泡泡足球', '玩家穿著泡泡足球裝備，進行球賽，分數依照進球數量計算，並且有多個小關卡以增加難度', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- 資料表結構 `images`
--

CREATE TABLE `images` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `image` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `scores`
--

CREATE TABLE `scores` (
  `team_id` int(11) DEFAULT NULL,
  `activity_id` int(11) DEFAULT NULL,
  `stage_id` int(11) DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `scores`
--

INSERT INTO `scores` (`team_id`, `activity_id`, `stage_id`, `score`, `timestamp`) VALUES
(1, 1, 1, 85.00, '2024-11-24 08:30:00'),
(1, 1, 2, 60.00, '2024-11-24 09:00:00'),
(2, 1, 1, 78.00, '2024-11-24 08:35:00'),
(2, 1, 2, 82.00, '2024-11-24 09:05:00'),
(3, 2, 1, 92.00, '2024-11-24 08:45:00'),
(3, 2, 2, 95.00, '2024-11-24 09:15:00'),
(4, 3, 1, 87.00, '2024-11-24 08:50:00'),
(4, 3, 2, 91.00, '2024-11-24 09:25:00'),
(6, 4, 1, 88.00, '2024-11-24 09:10:00'),
(6, 4, 2, 91.00, '2024-11-24 09:40:00'),
(5, NULL, NULL, 190.00, '2024-12-05 20:21:42'),
(1, 1, 3, 95.00, '2024-12-08 19:08:50'),
(1, 1, 4, 95.00, '2024-12-08 19:08:51'),
(1, 1, 5, 95.00, '2024-12-08 19:08:51');

-- --------------------------------------------------------

--
-- 資料表結構 `stage`
--

CREATE TABLE `stage` (
  `stage_id` int(11) NOT NULL,
  `stage_name` char(10) DEFAULT NULL,
  `activity_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `stage`
--

INSERT INTO `stage` (`stage_id`, `stage_name`, `activity_id`) VALUES
(1, '水球傳遞', 1),
(2, '水球避破', 1),
(3, '最終到達', 1),
(4, '越過障礙', 2),
(5, '穿越隧道', 2),
(6, '終點衝刺', 2),
(7, '擊中目標', 3),
(8, '準確射擊', 3),
(9, '防守反擊', 3),
(10, '找到物品', 4),
(11, '找到人物', 4),
(12, '快速尋找', 4),
(13, '命中目標', 5),
(14, '精準投擲', 5),
(15, '最遠距離', 5),
(16, '進球數', 6),
(17, '阻擋對手', 6),
(18, '協作進球', 6);

-- --------------------------------------------------------

--
-- 資料表結構 `teams`
--

CREATE TABLE `teams` (
  `team_id` int(11) NOT NULL,
  `team_name` char(20) DEFAULT NULL,
  `team_image` blob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `teams`
--

INSERT INTO `teams` (`team_id`, `team_name`, `team_image`) VALUES
(1, '甜甜圈戰士', NULL),
(2, '跳跳糖英雄', NULL),
(3, '可樂大軍', NULL),
(4, '洞穴哥布林', NULL),
(5, '紫色泡泡', NULL),
(6, '火焰小狐狸', NULL),
(7, '藍色海豚', NULL),
(8, '綠野精靈', NULL),
(9, '快樂小丑', NULL),
(10, '魔法蘑菇', NULL);

-- --------------------------------------------------------

--
-- 資料表結構 `team_members`
--

CREATE TABLE `team_members` (
  `member_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `member_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 傾印資料表的資料 `team_members`
--

INSERT INTO `team_members` (`member_id`, `team_id`, `member_name`) VALUES
(1, 1, '王大明'),
(2, 1, '陳小芳'),
(3, 1, '林志傑'),
(4, 2, '李佳穎'),
(5, 2, '張文華'),
(6, 2, '許建國'),
(7, 3, '吳怡靜'),
(8, 3, '鄭宏翔'),
(9, 3, '趙元昊'),
(10, 4, '蔡佩玲'),
(11, 4, '范庭豪'),
(12, 4, '高欣怡'),
(13, 5, '劉家豪'),
(14, 5, '黃詩涵'),
(15, 5, '柯俊傑'),
(16, 6, '邱宥廷'),
(17, 6, '曾婉婷'),
(18, 6, '葉信宇'),
(19, 7, '梁佳慧'),
(20, 7, '陸志強'),
(21, 7, '龔思潔'),
(22, 8, '蕭雅婷'),
(23, 8, '戴國強'),
(24, 8, '潘嘉文'),
(25, 9, '馮志華'),
(26, 9, '徐佩樺'),
(27, 9, '洪玉婷'),
(28, 10, '謝庭軒'),
(29, 10, '簡佳儀'),
(30, 10, '鍾明軒');

-- --------------------------------------------------------

--
-- 資料表結構 `user`
--

CREATE TABLE `user` (
  `account` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `password` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'm',
  `name` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- 傾印資料表的資料 `user`
--

INSERT INTO `user` (`account`, `password`, `role`, `name`, `date`) VALUES
('alice', 'm', 'l', '', '2024-12-05'),
('fg', 'vbg', 'l', '', '2024-12-05'),
('liuzixian', 'z', 'l', '', '2024-12-05'),
('peijie', 'S', 'l', '', '2024-12-05'),
('root', 'password', 'm', '', '2024-12-05');

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`activity_id`),
  ADD UNIQUE KEY `activity_name` (`activity_name`);

--
-- 資料表索引 `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`);

--
-- 資料表索引 `scores`
--
ALTER TABLE `scores`
  ADD KEY `team_id` (`team_id`),
  ADD KEY `activity_id` (`activity_id`),
  ADD KEY `stage_id` (`stage_id`);

--
-- 資料表索引 `stage`
--
ALTER TABLE `stage`
  ADD PRIMARY KEY (`stage_id`),
  ADD KEY `activity_id` (`activity_id`);

--
-- 資料表索引 `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`team_id`),
  ADD UNIQUE KEY `team_name` (`team_name`);

--
-- 資料表索引 `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`member_id`),
  ADD KEY `team_id` (`team_id`);

--
-- 資料表索引 `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`account`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `activities`
--
ALTER TABLE `activities`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `images`
--
ALTER TABLE `images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `stage`
--
ALTER TABLE `stage`
  MODIFY `stage_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `teams`
--
ALTER TABLE `teams`
  MODIFY `team_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `team_members`
--
ALTER TABLE `team_members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `scores`
--
ALTER TABLE `scores`
  ADD CONSTRAINT `scores_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scores_ibfk_2` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`activity_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `scores_ibfk_3` FOREIGN KEY (`stage_id`) REFERENCES `stage` (`stage_id`) ON UPDATE CASCADE;

--
-- 資料表的限制式 `stage`
--
ALTER TABLE `stage`
  ADD CONSTRAINT `stage_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`activity_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 資料表的限制式 `team_members`
--
ALTER TABLE `team_members`
  ADD CONSTRAINT `team_members_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
