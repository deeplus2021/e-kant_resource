/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 100411
 Source Host           : localhost:3306
 Source Schema         : db_ekant

 Target Server Type    : MySQL
 Target Server Version : 100411
 File Encoding         : 65001

 Date: 12/06/2021 09:22:17
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for failed_jobs
-- ----------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp(0) NOT NULL DEFAULT current_timestamp(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `failed_jobs_uuid_unique`(`uuid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of failed_jobs
-- ----------------------------

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 22 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of migrations
-- ----------------------------
INSERT INTO `migrations` VALUES (1, '2014_10_12_100000_create_password_resets_table', 1);
INSERT INTO `migrations` VALUES (2, '2016_06_01_000001_create_oauth_auth_codes_table', 1);
INSERT INTO `migrations` VALUES (3, '2016_06_01_000002_create_oauth_access_tokens_table', 1);
INSERT INTO `migrations` VALUES (4, '2016_06_01_000003_create_oauth_refresh_tokens_table', 1);
INSERT INTO `migrations` VALUES (5, '2016_06_01_000004_create_oauth_clients_table', 1);
INSERT INTO `migrations` VALUES (6, '2016_06_01_000005_create_oauth_personal_access_clients_table', 1);
INSERT INTO `migrations` VALUES (7, '2019_08_19_000000_create_failed_jobs_table', 1);
INSERT INTO `migrations` VALUES (8, '2020_10_07_140050_create_t_staff_status_table', 1);
INSERT INTO `migrations` VALUES (9, '2020_10_08_022918_create_t_staff_roles_table', 1);
INSERT INTO `migrations` VALUES (10, '2020_10_08_030000_create_t_staff_table', 1);
INSERT INTO `migrations` VALUES (11, '2020_10_08_030600_create_t_page_menu_table', 1);
INSERT INTO `migrations` VALUES (12, '2020_10_08_030601_create_t_role_permissions_table', 1);
INSERT INTO `migrations` VALUES (13, '2020_10_09_182559_create_t_field_table', 1);
INSERT INTO `migrations` VALUES (14, '2020_10_09_183501_create_t_staff_address_table', 1);
INSERT INTO `migrations` VALUES (15, '2020_10_09_184206_create_t_staff_field_table', 1);
INSERT INTO `migrations` VALUES (16, '2020_10_09_184616_create_t_post_table', 1);
INSERT INTO `migrations` VALUES (17, '2020_10_09_184719_create_t_shift_table', 1);
INSERT INTO `migrations` VALUES (18, '2020_10_09_185935_create_t_field_file_table', 1);
INSERT INTO `migrations` VALUES (19, '2020_10_09_190842_create_t_holiday_table', 1);
INSERT INTO `migrations` VALUES (20, '2020_10_16_093913_create_t_post_time_table', 1);
INSERT INTO `migrations` VALUES (21, '2020_11_05_060007_create_notifications_table', 1);

-- ----------------------------
-- Table structure for notifications
-- ----------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications`  (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp(0) NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `notifications_notifiable_type_notifiable_id_index`(`notifiable_type`, `notifiable_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of notifications
-- ----------------------------

-- ----------------------------
-- Table structure for oauth_access_tokens
-- ----------------------------
DROP TABLE IF EXISTS `oauth_access_tokens`;
CREATE TABLE `oauth_access_tokens`  (
  `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `scopes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  `expires_at` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `oauth_access_tokens_user_id_index`(`user_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of oauth_access_tokens
-- ----------------------------

-- ----------------------------
-- Table structure for oauth_auth_codes
-- ----------------------------
DROP TABLE IF EXISTS `oauth_auth_codes`;
CREATE TABLE `oauth_auth_codes`  (
  `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `scopes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `oauth_auth_codes_user_id_index`(`user_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of oauth_auth_codes
-- ----------------------------

-- ----------------------------
-- Table structure for oauth_clients
-- ----------------------------
DROP TABLE IF EXISTS `oauth_clients`;
CREATE TABLE `oauth_clients`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `provider` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `redirect` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `oauth_clients_user_id_index`(`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of oauth_clients
-- ----------------------------
INSERT INTO `oauth_clients` VALUES (1, NULL, 'E_KANT_ADMIN Personal Access Client', 'E6tQh9sNnn6f5HnKMgbkiPdiQ3Aj0e1ArPGqybMS', NULL, 'http://localhost', 1, 0, 0, '2021-06-12 09:21:26', '2021-06-12 09:21:26');
INSERT INTO `oauth_clients` VALUES (2, NULL, 'E_KANT_ADMIN Password Grant Client', 'UDHOE9n13CGsyNlNKUNQpPpgClspPO9DOJl8715c', 'staffs', 'http://localhost', 0, 1, 0, '2021-06-12 09:21:28', '2021-06-12 09:21:28');

-- ----------------------------
-- Table structure for oauth_personal_access_clients
-- ----------------------------
DROP TABLE IF EXISTS `oauth_personal_access_clients`;
CREATE TABLE `oauth_personal_access_clients`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of oauth_personal_access_clients
-- ----------------------------
INSERT INTO `oauth_personal_access_clients` VALUES (1, 1, '2021-06-12 09:21:26', '2021-06-12 09:21:26');

-- ----------------------------
-- Table structure for oauth_refresh_tokens
-- ----------------------------
DROP TABLE IF EXISTS `oauth_refresh_tokens`;
CREATE TABLE `oauth_refresh_tokens`  (
  `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `oauth_refresh_tokens_access_token_id_index`(`access_token_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of oauth_refresh_tokens
-- ----------------------------

-- ----------------------------
-- Table structure for password_resets
-- ----------------------------
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets`  (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  INDEX `password_resets_email_index`(`email`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of password_resets
-- ----------------------------

-- ----------------------------
-- Table structure for t_field
-- ----------------------------
DROP TABLE IF EXISTS `t_field`;
CREATE TABLE `t_field`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '現場コード',
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '現場名',
  `furigana` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ふりがな',
  `tel` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '電話番号',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '所在地',
  `latitude` double NOT NULL COMMENT '緯度',
  `longitude` double NOT NULL COMMENT '経度',
  `s_time` smallint(6) NOT NULL COMMENT '開始時間',
  `e_time` smallint(6) NOT NULL COMMENT '終了時間',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `created_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `updated_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `updated_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of t_field
-- ----------------------------

-- ----------------------------
-- Table structure for t_field_file
-- ----------------------------
DROP TABLE IF EXISTS `t_field_file`;
CREATE TABLE `t_field_file`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `field_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '現場コード',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ファイル名',
  `path` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ファイル保存場所',
  `created_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `created_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `updated_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `updated_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `t_field_file_field_id_foreign`(`field_id`) USING BTREE,
  CONSTRAINT `t_field_file_field_id_foreign` FOREIGN KEY (`field_id`) REFERENCES `t_field` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of t_field_file
-- ----------------------------

-- ----------------------------
-- Table structure for t_holiday
-- ----------------------------
DROP TABLE IF EXISTS `t_holiday`;
CREATE TABLE `t_holiday`  (
  `field_id` bigint(20) UNSIGNED NOT NULL COMMENT '現場コード',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '日',
  `h_date` date NOT NULL COMMENT '日',
  `created_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `created_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `updated_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `updated_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  INDEX `t_holiday_field_id_foreign`(`field_id`) USING BTREE,
  CONSTRAINT `t_holiday_field_id_foreign` FOREIGN KEY (`field_id`) REFERENCES `t_field` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of t_holiday
-- ----------------------------

-- ----------------------------
-- Table structure for t_page_menu
-- ----------------------------
DROP TABLE IF EXISTS `t_page_menu`;
CREATE TABLE `t_page_menu`  (
  `id` smallint(5) UNSIGNED NOT NULL,
  `parent_id` smallint(5) UNSIGNED NOT NULL,
  `order` smallint(6) NULL DEFAULT NULL,
  `code` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `url` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `button_type` varchar(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `image_url` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `desc` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `version` tinyint(4) NOT NULL DEFAULT 1,
  `created_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `created_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `updated_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `updated_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of t_page_menu
-- ----------------------------
INSERT INTO `t_page_menu` VALUES (0, 0, 1, 'all', 'all', 'index', '0', NULL, NULL, 1, NULL, NULL, NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_page_menu` VALUES (1, 0, 2, 'OpenStatusTable', 'オープン状況一覧', 'open-status-table', '0', NULL, NULL, 1, NULL, NULL, NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_page_menu` VALUES (2, 1, 1, 'OpenStatusTableIndex', 'オープン状況一覧', 'open-status-table/index', '0', NULL, NULL, 1, NULL, NULL, NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_page_menu` VALUES (3, 0, 3, 'AttendanceTable', '勤怠一覧', 'attendance-table', '0', NULL, NULL, 1, NULL, NULL, NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_page_menu` VALUES (4, 3, 1, 'AttendanceTableIndex', '勤怠一覧', 'attendance-table/index', '0', NULL, NULL, 1, NULL, NULL, NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_page_menu` VALUES (5, 0, 4, 'WorkTable', '出勤状況一覧', 'work-table', '0', NULL, NULL, 1, NULL, NULL, NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_page_menu` VALUES (6, 5, 1, 'WorkTableIndex', '出勤状況一覧', 'work-table/index', '0', NULL, NULL, 1, NULL, NULL, NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_page_menu` VALUES (7, 0, 5, 'PostTable', '配置ポスト表', 'post-table', '0', NULL, NULL, 1, NULL, NULL, NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_page_menu` VALUES (8, 7, 1, 'PostTableIndex', '配置ポスト表', 'post-table/index', '0', NULL, NULL, 1, NULL, NULL, NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_page_menu` VALUES (9, 0, 6, 'ShiftMaster', 'シフトマスタ', 'shift-master', '0', NULL, NULL, 1, NULL, NULL, NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_page_menu` VALUES (10, 9, 1, 'ShiftMasterIndex', 'シフトマスタ', 'shift-master/index', '0', NULL, NULL, 1, NULL, NULL, NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_page_menu` VALUES (11, 0, 7, 'FieldMaster', '現場マスタ', 'field-master', '0', NULL, NULL, 1, NULL, NULL, NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_page_menu` VALUES (12, 11, 1, 'FieldMasterIndex', '現場マスタ', 'field-master/index', '0', NULL, NULL, 1, NULL, NULL, NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_page_menu` VALUES (13, 0, 8, 'StaffMaster', 'スタッフマスタ', 'staff-master', '0', NULL, NULL, 1, NULL, NULL, NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_page_menu` VALUES (14, 13, 1, 'StaffMasterIndex', 'スタッフマスタ', 'staff-master/index', '0', NULL, NULL, 1, NULL, NULL, NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');

-- ----------------------------
-- Table structure for t_post
-- ----------------------------
DROP TABLE IF EXISTS `t_post`;
CREATE TABLE `t_post`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `field_id` bigint(20) UNSIGNED NOT NULL COMMENT '現場コード',
  `p_week` smallint(6) NULL DEFAULT NULL COMMENT '曜日:1-日　2-月　3-火　4-水　5-木　6-金　7-土',
  `s_date` date NULL DEFAULT NULL COMMENT '日',
  `e_date` date NULL DEFAULT NULL COMMENT '日',
  `created_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `created_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `updated_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `updated_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `t_post_field_id_foreign`(`field_id`) USING BTREE,
  CONSTRAINT `t_post_field_id_foreign` FOREIGN KEY (`field_id`) REFERENCES `t_field` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of t_post
-- ----------------------------

-- ----------------------------
-- Table structure for t_post_time
-- ----------------------------
DROP TABLE IF EXISTS `t_post_time`;
CREATE TABLE `t_post_time`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `s_time` smallint(6) NOT NULL COMMENT '開始時間',
  `e_time` smallint(6) NOT NULL COMMENT '終了時間',
  `number` smallint(6) NOT NULL DEFAULT 0 COMMENT '人数',
  `created_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `created_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `updated_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `updated_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `t_post_time_post_id_foreign`(`post_id`) USING BTREE,
  CONSTRAINT `t_post_time_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `t_post` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of t_post_time
-- ----------------------------

-- ----------------------------
-- Table structure for t_role_permissions
-- ----------------------------
DROP TABLE IF EXISTS `t_role_permissions`;
CREATE TABLE `t_role_permissions`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '角色权限关系_id',
  `staff_role_id` tinyint(3) UNSIGNED NOT NULL COMMENT '角色_id',
  `page_menu_id` smallint(5) UNSIGNED NOT NULL COMMENT '页面_id',
  `created_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `created_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `updated_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `updated_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `t_role_permissions_staff_role_id_foreign`(`staff_role_id`) USING BTREE,
  INDEX `t_role_permissions_page_menu_id_foreign`(`page_menu_id`) USING BTREE,
  CONSTRAINT `t_role_permissions_page_menu_id_foreign` FOREIGN KEY (`page_menu_id`) REFERENCES `t_page_menu` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `t_role_permissions_staff_role_id_foreign` FOREIGN KEY (`staff_role_id`) REFERENCES `t_staff_roles` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of t_role_permissions
-- ----------------------------
INSERT INTO `t_role_permissions` VALUES (1, 2, 1, NULL, '127.0.0.1', NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_role_permissions` VALUES (2, 2, 2, NULL, '127.0.0.1', NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_role_permissions` VALUES (3, 2, 3, NULL, '127.0.0.1', NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_role_permissions` VALUES (4, 2, 4, NULL, '127.0.0.1', NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_role_permissions` VALUES (5, 2, 5, NULL, '127.0.0.1', NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_role_permissions` VALUES (6, 2, 6, NULL, '127.0.0.1', NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_role_permissions` VALUES (7, 2, 7, NULL, '127.0.0.1', NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_role_permissions` VALUES (8, 2, 8, NULL, '127.0.0.1', NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_role_permissions` VALUES (9, 2, 9, NULL, '127.0.0.1', NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_role_permissions` VALUES (10, 2, 10, NULL, '127.0.0.1', NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_role_permissions` VALUES (11, 2, 11, NULL, '127.0.0.1', NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_role_permissions` VALUES (12, 2, 12, NULL, '127.0.0.1', NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_role_permissions` VALUES (13, 2, 13, NULL, '127.0.0.1', NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_role_permissions` VALUES (14, 2, 14, NULL, '127.0.0.1', NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_role_permissions` VALUES (15, 3, 1, NULL, '127.0.0.1', NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_role_permissions` VALUES (16, 3, 2, NULL, '127.0.0.1', NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_role_permissions` VALUES (17, 3, 13, NULL, '127.0.0.1', NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_role_permissions` VALUES (18, 3, 14, NULL, '127.0.0.1', NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_role_permissions` VALUES (19, 4, 1, NULL, '127.0.0.1', NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_role_permissions` VALUES (20, 4, 2, NULL, '127.0.0.1', NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_role_permissions` VALUES (21, 4, 13, NULL, '127.0.0.1', NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_role_permissions` VALUES (22, 4, 14, NULL, '127.0.0.1', NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');

-- ----------------------------
-- Table structure for t_shift
-- ----------------------------
DROP TABLE IF EXISTS `t_shift`;
CREATE TABLE `t_shift`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `staff_id` bigint(20) UNSIGNED NOT NULL COMMENT 'スタッフコード',
  `field_id` bigint(20) UNSIGNED NOT NULL COMMENT '現場コード',
  `admin_id` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '承認者',
  `confirmed_at` timestamp(0) NULL DEFAULT NULL COMMENT '承認確認',
  `shift_date` date NOT NULL COMMENT '日付',
  `field_s_time` smallint(6) NULL DEFAULT NULL COMMENT '出社時間',
  `field_e_time` smallint(6) NULL DEFAULT NULL COMMENT '退社時間',
  `s_time` smallint(6) NULL DEFAULT NULL COMMENT 'シフト出社時間',
  `e_time` smallint(6) NULL DEFAULT NULL COMMENT 'シフト退社時間',
  `ks_time` smallint(6) NULL DEFAULT NULL COMMENT '休憩時間',
  `ke_time` smallint(6) NULL DEFAULT NULL COMMENT '休憩時間',
  `sks_time` smallint(6) NULL DEFAULT NULL COMMENT '深夜休憩時間',
  `ske_time` smallint(6) NULL DEFAULT NULL COMMENT '深夜休憩時間',
  `staff_status_id` tinyint(3) UNSIGNED NULL DEFAULT NULL COMMENT 'staff status',
  `yesterday_checked_at` timestamp(0) NULL DEFAULT NULL COMMENT '前日確認',
  `today_checked_at` timestamp(0) NULL DEFAULT NULL COMMENT '当日確認',
  `health_status` tinyint(1) NULL DEFAULT NULL COMMENT '健康状态',
  `start_checked_at` timestamp(0) NULL DEFAULT NULL COMMENT '出発確認',
  `arrive_checked_at` timestamp(0) NULL DEFAULT NULL COMMENT '到着確認',
  `leave_checked_at` timestamp(0) NULL DEFAULT NULL COMMENT '退勤済',
  `break_at` timestamp(0) NULL DEFAULT NULL COMMENT '休憩時間',
  `break_time` smallint(6) NULL DEFAULT NULL COMMENT '休憩時間',
  `night_break_at` timestamp(0) NULL DEFAULT NULL COMMENT '深夜休憩時間',
  `night_break_time` smallint(6) NULL DEFAULT NULL COMMENT '深夜休憩時間',
  `e_leave_time` smallint(6) NULL DEFAULT NULL COMMENT '早退時間',
  `e_leave_at` timestamp(0) NULL DEFAULT NULL COMMENT '早退申請時間',
  `e_leave_checked_at` timestamp(0) NULL DEFAULT NULL COMMENT '早退確認時間',
  `rest_at` timestamp(0) NULL DEFAULT NULL COMMENT '休日申請',
  `rest_checked_at` timestamp(0) NULL DEFAULT NULL COMMENT '休日承認',
  `over_time` smallint(6) NULL DEFAULT NULL COMMENT '残業時間',
  `over_time_at` timestamp(0) NULL DEFAULT NULL COMMENT '残業申請時間',
  `over_time_checked_at` timestamp(0) NULL DEFAULT NULL COMMENT '残業承認',
  `alt_date` date NULL DEFAULT NULL COMMENT '振替日',
  `alt_date_at` timestamp(0) NULL DEFAULT NULL COMMENT '振替日申請時間',
  `alt_date_checked_at` timestamp(0) NULL DEFAULT NULL COMMENT '振替日承認',
  `late_time` smallint(6) NULL DEFAULT NULL COMMENT '遅刻',
  `late_at` timestamp(0) NULL DEFAULT NULL COMMENT '遅刻',
  `late_checked_at` timestamp(0) NULL DEFAULT NULL COMMENT '遅刻',
  `arrive_changed_at` timestamp(0) NULL DEFAULT NULL,
  `arrive_changed_checked_at` timestamp(0) NULL DEFAULT NULL,
  `leave_changed_at` timestamp(0) NULL DEFAULT NULL,
  `leave_changed_checked_at` timestamp(0) NULL DEFAULT NULL,
  `break_changed_at` timestamp(0) NULL DEFAULT NULL,
  `break_changed_time` smallint(6) NULL DEFAULT NULL,
  `break_changed_checked_at` timestamp(0) NULL DEFAULT NULL,
  `night_break_changed_at` timestamp(0) NULL DEFAULT NULL,
  `night_break_changed_time` smallint(6) NULL DEFAULT NULL,
  `night_break_changed_checked_at` timestamp(0) NULL DEFAULT NULL,
  `created_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `created_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `updated_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `updated_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `t_shift_staff_id_foreign`(`staff_id`) USING BTREE,
  INDEX `t_shift_field_id_foreign`(`field_id`) USING BTREE,
  INDEX `t_shift_admin_id_foreign`(`admin_id`) USING BTREE,
  INDEX `t_shift_staff_status_id_foreign`(`staff_status_id`) USING BTREE,
  CONSTRAINT `t_shift_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `t_staff` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `t_shift_field_id_foreign` FOREIGN KEY (`field_id`) REFERENCES `t_field` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `t_shift_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `t_staff` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `t_shift_staff_status_id_foreign` FOREIGN KEY (`staff_status_id`) REFERENCES `t_staff_status` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of t_shift
-- ----------------------------

-- ----------------------------
-- Table structure for t_staff
-- ----------------------------
DROP TABLE IF EXISTS `t_staff`;
CREATE TABLE `t_staff`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'スタッフコード',
  `code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '社員コード',
  `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名前',
  `furigana` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'ふりがな',
  `email` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'メールアドレス',
  `email_verified_at` timestamp(0) NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'パスワード',
  `tel` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '電話番号',
  `staff_role_id` tinyint(3) UNSIGNED NOT NULL DEFAULT 3 COMMENT '権限',
  `holiday` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL COMMENT '原則休日登録日--1:月,2:火,3:水,4:木,5:金,6:土,7:日,8:祝日',
  `desired_holiday` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL COMMENT '希望休日',
  `yesterday_flag` tinyint(1) NOT NULL DEFAULT 1 COMMENT '前日確認',
  `today_flag` tinyint(1) NOT NULL DEFAULT 1 COMMENT '当日確認',
  `fcm_token` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'fcm token',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `created_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `updated_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `updated_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `t_staff_email_unique`(`email`) USING BTREE,
  INDEX `t_staff_staff_role_id_foreign`(`staff_role_id`) USING BTREE,
  CONSTRAINT `t_staff_staff_role_id_foreign` FOREIGN KEY (`staff_role_id`) REFERENCES `t_staff_roles` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of t_staff
-- ----------------------------
INSERT INTO `t_staff` VALUES (1, 'admin', 'SuperAdmin', 'スーパー管理者', 'superadmin@ekant.com', '2020-10-09 16:00:00', '$2y$10$5HPj7F/K2Jg9x6J51GYD9uaUNtiviqKlFK0X6X5T6TnQKn3BXIHE2', '11111111101', 1, '[6,7]', NULL, 1, 1, NULL, 1, NULL, NULL, NULL, NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');

-- ----------------------------
-- Table structure for t_staff_address
-- ----------------------------
DROP TABLE IF EXISTS `t_staff_address`;
CREATE TABLE `t_staff_address`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `staff_id` bigint(20) UNSIGNED NOT NULL COMMENT 'スタッフコード',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '出発先住所',
  `latitude` double NOT NULL COMMENT '緯度',
  `longitude` double NOT NULL COMMENT '経度',
  `field_id` bigint(20) UNSIGNED NOT NULL COMMENT '現場コード',
  `required_time` smallint(6) NOT NULL COMMENT '現場までの時間',
  `email_time` smallint(6) NOT NULL COMMENT '当日確認メール送信時間',
  `created_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `created_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `updated_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `updated_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `t_staff_address_field_id_foreign`(`field_id`) USING BTREE,
  INDEX `t_staff_address_staff_id_foreign`(`staff_id`) USING BTREE,
  CONSTRAINT `t_staff_address_field_id_foreign` FOREIGN KEY (`field_id`) REFERENCES `t_field` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `t_staff_address_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `t_staff` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of t_staff_address
-- ----------------------------

-- ----------------------------
-- Table structure for t_staff_field
-- ----------------------------
DROP TABLE IF EXISTS `t_staff_field`;
CREATE TABLE `t_staff_field`  (
  `staff_id` bigint(20) UNSIGNED NOT NULL COMMENT 'スタッフコード',
  `field_id` bigint(20) UNSIGNED NOT NULL COMMENT '現場コード',
  `created_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `created_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `updated_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `updated_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  INDEX `t_staff_field_staff_id_foreign`(`staff_id`) USING BTREE,
  INDEX `t_staff_field_field_id_foreign`(`field_id`) USING BTREE,
  CONSTRAINT `t_staff_field_field_id_foreign` FOREIGN KEY (`field_id`) REFERENCES `t_field` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `t_staff_field_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `t_staff` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of t_staff_field
-- ----------------------------

-- ----------------------------
-- Table structure for t_staff_roles
-- ----------------------------
DROP TABLE IF EXISTS `t_staff_roles`;
CREATE TABLE `t_staff_roles`  (
  `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '角色_id',
  `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '角色名称',
  `desc` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '描述',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否有效 0:无效; 1:有效',
  `created_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `created_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `updated_by` int(10) UNSIGNED NULL DEFAULT NULL,
  `updated_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT NULL,
  `updated_at` timestamp(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of t_staff_roles
-- ----------------------------
INSERT INTO `t_staff_roles` VALUES (1, '管理者', '', 1, NULL, NULL, NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_staff_roles` VALUES (2, '現場責任者', '', 1, NULL, NULL, NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_staff_roles` VALUES (3, 'スタッフ', '', 1, NULL, NULL, NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');
INSERT INTO `t_staff_roles` VALUES (4, '緊急対応スタッフ', '', 1, NULL, NULL, NULL, '127.0.0.1', '2021-06-12 09:21:17', '2021-06-12 09:21:17');

-- ----------------------------
-- Table structure for t_staff_status
-- ----------------------------
DROP TABLE IF EXISTS `t_staff_status`;
CREATE TABLE `t_staff_status`  (
  `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'スタッフの状況',
  `sort` tinyint(4) NOT NULL DEFAULT 0,
  `desc` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of t_staff_status
-- ----------------------------
INSERT INTO `t_staff_status` VALUES (1, '準備中', 1, '前日確認メールがあった場合');
INSERT INTO `t_staff_status` VALUES (2, '接近中', 2, '出勤前で確認メール済の場合、決まった時間に自宅を出ている場合');
INSERT INTO `t_staff_status` VALUES (3, '勤務中', 3, '');
INSERT INTO `t_staff_status` VALUES (4, '退勤済', 4, '');
INSERT INTO `t_staff_status` VALUES (5, ' ', 5, '未来の場合');
INSERT INTO `t_staff_status` VALUES (6, '警告(自宅を出てない)', 6, '確認メール等をしてない、決まった時間に自宅を出てない場合');

SET FOREIGN_KEY_CHECKS = 1;
