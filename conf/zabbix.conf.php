<?php
// Zabbix GUI configuration file
global $DB;

$DB['TYPE']     = 'MYSQL';
#$DB['SERVER']   = '133.133.133.138';
$DB['SERVER']   = "133.133.135.149";
$DB['PORT']     = '3306';
$DB['DATABASE'] = 'zabbix';
$DB['USER']     = 'zabbix';
$DB['PASSWORD'] = '123456';

// SCHEMA is relevant only for IBM_DB2 database
$DB['SCHEMA'] = '';

$ZBX_SERVER      = '133.133.135.149';
$ZBX_SERVER_PORT = '10051';
$ZBX_SERVER_NAME = 'MonitorServer';

$IMAGE_FORMAT_DEFAULT = IMAGE_FORMAT_PNG;


//start wziyong custom  ftp configuration
global $FTP;
$FTP['FTP_HOST']="133.133.135.149";
$FTP['FTP_PORT']=21;
$FTP['FTP_USER']="myftp";
$FTP['FTP_PASS']="123456";
//end wziyong custom  ftp configuration



?>
