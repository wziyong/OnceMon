<?php
// Zabbix GUI configuration file
global $DB;

$DB['TYPE']     = 'MYSQL';
$DB['SERVER']   = '133.133.133.138';
$DB['PORT']     = '0';
$DB['DATABASE'] = 'zabbix';
$DB['USER']     = 'zabbix';
$DB['PASSWORD'] = '123456';

// SCHEMA is relevant only for IBM_DB2 database
$DB['SCHEMA'] = '';

$ZBX_SERVER      = '133.133.135.13';
$ZBX_SERVER_PORT = '10051';
$ZBX_SERVER_NAME = 'OnceMonServer';

$IMAGE_FORMAT_DEFAULT = IMAGE_FORMAT_PNG;
?>
