<?php
/*
** OnceMon
** Copyright (C) 2014-2015 ISCAS
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


//配置模板文件；
$hostCfgFormList = $this->data['hostList'];
$hostservercfgs = $this->data['hostservercfgs'];

// form list
$hostCfgFormList->addRow(null, array(new CLabel('服务器配置')), null, 'label_server_cfg', 'new');
$isDiscovered = false;
//application server configuration
$lbslistenportTBkey = new CTextBox('appcfg[0][name]', '', ZBX_TEXTBOX_STANDARD_SIZE, $isDiscovered);
$lbslistenportTBkey->setAttribute('value', 'app_http_port');
$hostCfgFormList->addRow(_('HTTP端口key'), $lbslistenportTBkey, true, null, null);
$apphttpportTBx = new CTextBox('appcfg[0][value]', !empty($hostservercfgs['app_http_port']) ? $hostservercfgs['app_http_port'] : null, ZBX_TEXTBOX_STANDARD_SIZE, $isDiscovered);
$apphttpportTBx->setAttribute('maxlength', 64);
$apphttpportTBx->setAttribute('autofocus', 'autofocus');
$hostCfgFormList->addRow(_('HTTP端口'), $apphttpportTBx, false, null, 'app_server');

$lbslistenportTBkey = new CTextBox('appcfg[1][name]', '', ZBX_TEXTBOX_STANDARD_SIZE, $isDiscovered);
$lbslistenportTBkey->setAttribute('value', 'app_connect_timeout');
$hostCfgFormList->addRow(_('链接超时key'), $lbslistenportTBkey, true, null, null);
$apphttpportTBx = new CTextBox('appcfg[1][value]', !empty($hostservercfgs['app_connect_timeout']) ? $hostservercfgs['app_connect_timeout'] : null, ZBX_TEXTBOX_STANDARD_SIZE, $isDiscovered);
$apphttpportTBx->setAttribute('maxlength', 64);
$apphttpportTBx->setAttribute('autofocus', 'autofocus');
$hostCfgFormList->addRow(_('连接超时(毫秒)'), $apphttpportTBx, false, null, 'app_server');


$lbslistenportTBkey = new CTextBox('appcfg[2][name]', '', ZBX_TEXTBOX_STANDARD_SIZE, $isDiscovered);
$lbslistenportTBkey->setAttribute('value', 'app_max_threads');
$hostCfgFormList->addRow(_('最大连接池key'), $lbslistenportTBkey, true, null, null);
$apphttpportTBx = new CTextBox('appcfg[2][value]', !empty($hostservercfgs['app_max_threads']) ? $hostservercfgs['app_max_threads'] : null, ZBX_TEXTBOX_STANDARD_SIZE, $isDiscovered);
$apphttpportTBx->setAttribute('maxlength', 64);
$apphttpportTBx->setAttribute('autofocus', 'autofocus');
$hostCfgFormList->addRow(_('最大连接池'), $apphttpportTBx, false, null, 'app_server');


//start load balance server configuration
$lbslistenportTBkey = new CTextBox('lbscfg[0][name]', '', ZBX_TEXTBOX_STANDARD_SIZE, $isDiscovered);
$lbslistenportTBkey->setAttribute('value', 'lbs_listen_port');
$hostCfgFormList->addRow(_('监听端口key'), $lbslistenportTBkey, true, null, null);
$lbslistenportTBx = new CTextBox('lbscfg[0][value]', empty($hostservercfgs['lbs_listen_port']) ? null : $hostservercfgs['lbs_listen_port'], ZBX_TEXTBOX_STANDARD_SIZE, $isDiscovered);
$lbslistenportTBx->setAttribute('maxlength', 64);
$lbslistenportTBx->setAttribute('autofocus', 'autofocus');
$hostCfgFormList->addRow(_('监听端口'), $lbslistenportTBx, false, null, 'lbs_server');


$lbslistenportTBkey = new CTextBox('lbscfg[2][name]', '', ZBX_TEXTBOX_STANDARD_SIZE, $isDiscovered);
$lbslistenportTBkey->setAttribute('value', 'lbs_upstream_type');
$hostCfgFormList->addRow(_('负载策略key'), $lbslistenportTBkey, true, null, null);
$lbs_upstream_type = new CComboBox('lbscfg[2][value]', empty($hostservercfgs['lbs_upstream_type']) ? null : $hostservercfgs['lbs_upstream_type']);
$lbs_upstream_type->addStyle('width: 330px;');
$lbs_upstream_type->addItem(0, 'RR(轮询)');
$lbs_upstream_type->addItem(1, 'Weight(权重)');
$lbs_upstream_type->addItem(2, 'IP Hash');
$lbs_upstream_type->addItem(3, 'Least_conn(最少连接数)');
$lbs_upstream_type->addItem(4, 'Consistent Hash(一致性算法)');
$hostCfgFormList->addRow(_('负载策略'), $lbs_upstream_type, false, null, 'lbs_server');

return $hostCfgFormList;
