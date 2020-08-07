DROP TABLE IF EXISTS `cms_sms_log`;

CREATE TABLE `cms_sms_log` (
  `id` INT(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT,
  `operator` VARCHAR(80) NOT NULL COMMENT '运营商',
  `template` TEXT NOT NULL COMMENT '短信模板ID',
  `recv` TEXT NOT NULL COMMENT '接收人',
  `param` TEXT COMMENT '短信模板变量',
  `sendtime` VARCHAR(80) COMMENT '发送时间',
  `result` TEXT COMMENT '发送结果',
  PRIMARY KEY (`id`)
)ENGINE = InnoDB DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS `cms_sms_operator`;

CREATE TABLE `cms_sms_operator` (
  `id` INT(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT,
  `name` VARCHAR(80) NOT NULL COMMENT '运营商名称',
  `tablename` VARCHAR(80) NOT NULL COMMENT '表名',
  `remark` VARCHAR(255) COMMENT '描述',
  `enable` TINYINT(4) DEFAULT 0 COMMENT '是否启用',
  PRIMARY KEY (`id`)
)ENGINE = InnoDB DEFAULT CHARSET = utf8;

DROP TABLE IF EXISTS `cms_sms_aliyun`;
-- 阿里云短信服务
CREATE TABLE `cms_sms_aliyun` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `access_id` varchar(255) DEFAULT '' COMMENT 'Access Key ID（阿里云API密钥）',
  `access_key` varchar(255) DEFAULT '' COMMENT 'Access Key Secret（阿里云API密钥）',
  `sign` varchar(255) DEFAULT '' COMMENT '短信签名',
  `template` varchar(255) DEFAULT '' COMMENT '短信模版 Code',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '模板内容',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '别名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `cms_sms_alibabacloud_mainland`;
-- 国际版阿里云短信服务（大陆短信）
CREATE TABLE `cms_sms_alibabacloud_mainland`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `access_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '通过阿里云申请可得',
  `access_key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '通过阿里云申请',
  `is_open` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '1' COMMENT '是否开启 1为开启 0为关闭',
  `sign` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '短信签名',
  `template` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '短信CODE',
  `about` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '介绍',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '别名',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;


DROP TABLE IF EXISTS `cms_sms_alibabacloud_abroad`;
-- 国际版阿里云短信服务（国际短信）
CREATE TABLE `cms_sms_alibabacloud_abroad`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `access_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '通过阿里云申请可得',
  `access_key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '通过阿里云申请',
  `is_open` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '1' COMMENT '是否开启 1为开启 0为关闭',
  `alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '别名',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

INSERT INTO `cms_sms_operator` (`id`, `name`, `tablename`, `remark`, `enable`) VALUES ('1', '阿里云短信服务', 'aliyun', '阿里云短信服务', '1');
INSERT INTO `cms_sms_operator` (`id`, `name`, `tablename`, `remark`, `enable`) VALUES ('2', '国际版阿里云短信服务（大陆短信）', 'alibabacloud_mainland', '国际版阿里云短信服务（大陆短信）', '0');
INSERT INTO `cms_sms_operator` (`id`, `name`, `tablename`, `remark`, `enable`) VALUES ('3', '国际版阿里云短信服务（国际短信）', 'alibabacloud_abroad', '国际版阿里云短信服务（国际短信）', '0');