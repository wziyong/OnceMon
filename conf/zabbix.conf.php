<?php
// Zabbix GUI configuration file
global $DB;

$DB['TYPE']     = 'MYSQL';
$DB['SERVER']   = '133.133.135.13';
$DB['PORT']     = '3306';
$DB['DATABASE'] = 'zabbix';
$DB['USER']     = 'root';
$DB['PASSWORD'] = 'onceas';

// SCHEMA is relevant only for IBM_DB2 database
$DB['SCHEMA'] = '';

$ZBX_SERVER      = '133.133.135.13';
$ZBX_SERVER_PORT = '10051';
$ZBX_SERVER_NAME = 'zabbix-server';

$IMAGE_FORMAT_DEFAULT = IMAGE_FORMAT_PNG;
?>
