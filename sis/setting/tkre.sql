/*
 Navicat Premium Data Transfer

 Source Server         : 5.1
 Source Server Type    : MySQL
 Source Server Version : 100513
 Source Host           : 192.168.5.1:3306
 Source Schema         : kphis

 Target Server Type    : MySQL
 Target Server Version : 100513
 File Encoding         : 65001

 Date: 06/10/2022 18:41:49
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ttnd
-- ----------------------------
DROP TABLE IF EXISTS `tkre`;
CREATE TABLE `tkre`  (
  `surgery_No` int NOT NULL AUTO_INCREMENT,
  `loginname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `doctorname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `an` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `hn` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `dateofoperation` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `timestart` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `timeended` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `surgeon` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `firstassistant` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `seconddiagnosis` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `surgicalnurse` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `clinicaldiagnosis` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `postoperativediagnosis` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `operation` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `anesthesia` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `anesthesist`text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `operative` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `checkclick` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `checkyestext` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `date` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
 
  PRIMARY KEY (`surgery_No`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

SET FOREIGN_KEY_CHECKS = 1;