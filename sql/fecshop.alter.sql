ALTER TABLE `customer`
ADD COLUMN `ecs_user_id`  int(11) NULL DEFAULT NULL COMMENT 'ecshop users user_id' AFTER `is_validated`,
ADD COLUMN `ec_salt`  varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'ecshop salt' AFTER `ecs_user_id`,
ADD COLUMN `pay_points`  int(11) NOT NULL DEFAULT 0 COMMENT '支付积分' AFTER `ec_salt`,
ADD COLUMN `rank_points`  int(11) NOT NULL DEFAULT 0 COMMENT '等级积分' AFTER `pay_points`,
ADD COLUMN `country`  varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '所属国家' AFTER `rank_points`,
ADD COLUMN `referer`  varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '注册来源' AFTER `country`,
ADD COLUMN `is_validated`  int(5) NOT NULL DEFAULT 0 COMMENT '邮箱是否验证 0 否, 1是' AFTER `referer`;