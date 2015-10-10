ALTER TABLE `groups`
ADD COLUMN `comment` VARCHAR(256) NULL COMMENT '备注';
ALTER TABLE `groups`
CHANGE COLUMN `ishidden` `ishiddenx` VARCHAR(45) NULL DEFAULT '1' COMMENT '0 不可见；1可见；' ;
update groups set ishidden = '0' where groupid in (1,2,4,5,6,7);
commit;


ALTER TABLE `hosts`
ADD COLUMN `server_type` INT(11)  COMMENT '服务器类型，0：负载均衡器；1：应用服务器；2：缓存服务器；' AFTER `templateid`;
ALTER TABLE `hosts_groups`
ADD COLUMN `parentId` BIGINT(20) NULL COMMENT '父主机节点；一般为负载均衡器的id；' AFTER `groupid`;


CREATE TABLE `t_custom_hostconfig` (
  `hostconfigid` bigint(20) unsigned NOT NULL,
  `hostid` bigint(20) DEFAULT NULL COMMENT '主机di',
  `type` int(11) DEFAULT NULL,
  `name` varchar(256) DEFAULT NULL,
  `value` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`hostconfigid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='定制表：主机参数列表；';



CREATE TABLE `t_custom_myapplication` (
  `applicationid` BIGINT(20) UNSIGNED NOT NULL,
  `name` VARCHAR(256) NOT NULL,
  `filename` VARCHAR(1024) NOT NULL ,
  `comment` VARCHAR(1024) NULL,
  PRIMARY KEY (`applicationid`));


