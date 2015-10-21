ALTER TABLE `groups`
ADD COLUMN `comment` VARCHAR(256) NULL COMMENT '备注';

ALTER TABLE `groups`
ADD COLUMN `ishidden`VARCHAR(45) NULL DEFAULT '1' COMMENT '0 不可见；1可见；' ;

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

CREATE TABLE `t_custom_hostapps` (
  `hostid` BIGINT(20) NOT NULL COMMENT '',
  `applicationid` BIGINT(20) NOT NULL COMMENT '');


ALTER TABLE `hosts`
ADD COLUMN `manage_status` INT(11) NULL DEFAULT 0 COMMENT '管理状态：0未同步（未同步默认为已结停止），1已启动，2已停止；' AFTER `server_type`;


INSERT INTO `hosts` (`hostid`,`proxy_hostid`,`host`,`status`,`ipmi_authtype`,`ipmi_privilege`,`ipmi_username`,`ipmi_password`,`name`,`flags`,`templateid`)
values ('10105',NULL,'Template OnceMon','3','0','2','','','Template OnceMon','0',NULL);
INSERT INTO `hosts_groups` (`hostgroupid`,`hostid`,`groupid`) values ('111','10105','1');
INSERT INTO `items` (`itemid`,`type`,`snmp_community`,`snmp_oid`,`hostid`,`name`,`key_`,`delay`,`history`,`trends`,`status`,`value_type`,`trapper_hosts`,`units`,`multiplier`,`delta`,`snmpv3_securityname`,`snmpv3_securitylevel`,`snmpv3_authpassphrase`,`snmpv3_privpassphrase`,`formula`,`error`,`lastlogsize`,`logtimefmt`,`templateid`,`valuemapid`,`delay_flex`,`params`,`ipmi_sensor`,`data_type`,`authtype`,`username`,`password`,`publickey`,`privatekey`,`mtime`,`flags`,`filter`,`interfaceid`,`port`,`description`,`inventory_link`,`lifetime`,`snmpv3_authprotocol`,`snmpv3_privprotocol`,`state`,`snmpv3_contextname`)
VALUES ('23672','0','','','10105','net.tcp.listen.run.status','net.tcp.listen[{$SERVER_PORT}]','30','90','365','0','0','','','0','0','','0','','','1','','0','',null,null,'','','','0','0','','','','','0','0','',null,'','','0','30','0','0','0','');
INSERT INTO `graphs` (`graphid`,`name`,`width`,`height`,`yaxismin`,`yaxismax`,`templateid`,`show_work_period`,`show_triggers`,`graphtype`,`show_legend`,`show_3d`,`percent_left`,`percent_right`,`ymin_type`,`ymax_type`,`ymin_itemid`,`ymax_itemid`,`flags`)
VALUES ('535','net.tcp.listen.run.status.graph','900','200','0.0000','100.0000',null,'1','1','0','1','0','0.0000','0.0000','0','0',null,null,'0');
INSERT INTO `graphs_items` (`gitemid`,`graphid`,`itemid`,`drawtype`,`sortorder`,`color`,`yaxisside`,`calc_fnc`,`type`)
values ('1825','535','23672','0','0','C80000','0','2','0');
commit;

