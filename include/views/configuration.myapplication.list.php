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


$myapplicationWidget = new CWidget();

// create new media type button
$createForm = new CForm('get');
$createForm->addItem(new CSubmit('form', _('创建应用')));
$myapplicationWidget->addPageHeader(_('应用管理'), $createForm);
$myapplicationWidget->addHeader(_('应用'));
$myapplicationWidget->addHeaderRowNumber();

// create form
$myapplicationForm = new CForm();
$myapplicationForm->setName('myapplicationForm');

// create table
$myapplicationTable = new CTableInfo(_('No media types found.'));
$myapplicationTable->setHeader(array(
	new CCheckBox('all_myapplication', null, "checkAll('".$myapplicationForm->getName()."', 'all_myapplication', 'applicationids');"),
	$this->data['displayNodes'] ? _('Node') : null,
	make_sorting_header(_('Name'), 'description'),
	_('安装包'),
	_('备注'),
	_('状态')
));

foreach ($this->data['myapplications'] as $myapplication) {
	$status = null;
	switch ($myapplication['status']) {
		case 0:
			$statusCaption = _('新增');
			$statusClass = 'enabled';
			$statusScript = 'void();';
			$statusUrl = 'hosts.php?hosts'.SQUAREBRACKETS.'='.$myapplication['applicationid'].'&go=disable'.url_param('groupid');
			$status = new CSpan($statusCaption);
			break;
		case 1:
			$statusCaption = _('统一更新部署');
			$statusUrl = 'myapplication.php?form=deploy&applicationid='.$myapplication['applicationid'];
			$statusUrl = 'myapplication.php?applicationids'.SQUAREBRACKETS.'='.$myapplication['applicationid'].'&go=deploy';
			$statusScript = 'return Confirm('.zbx_jsvalue(_('统一部署该应用到服务器上?')).');';
			$statusClass = 'orange';
			$status = new CLink($statusCaption, $statusUrl, $statusClass, $statusScript);
			break;
		case 2:
			$statusCaption = _('统一部署失败');
			$statusUrl = 'myapplication.php?applicationids'.SQUAREBRACKETS.'='.$myapplication['applicationid'].'&go=deploy';
			$statusScript = 'return Confirm('.zbx_jsvalue(_('Enable host?')).');';
			$statusClass = 'disabled';
			$status = new CLink($statusCaption, $statusUrl, $statusClass, $statusScript);
			break;
		case 3:
			$statusCaption = _('统一部署成功');
			$statusClass = 'enabled';
			$status = new CSpan($statusCaption, $statusClass);
			break;
	}



	// append row
	$myapplicationTable->addRow(array(
		new CCheckBox('applicationids['.$myapplication['applicationid'].']', null, null, $myapplication['applicationid']),
		new CLink($myapplication['name'], 'myapplication.php?form=update&applicationid='.$myapplication['applicationid']),
		$myapplication['filename'],
		$myapplication['comment'],
		$status,
	));
}

// create go button
$goComboBox = new CComboBox('go');

$goOption = new CComboItem('delete', _('Delete selected'));
$goOption->setAttribute('confirm', _('删除所选的应用?'));
$goComboBox->addItem($goOption);

$goButton = new CSubmit('goButton', _('Go').' (0)');
$goButton->setAttribute('id', 'goButton');
zbx_add_post_js('chkbxRange.pageGoName = "applicationids";');

// append table to form
$myapplicationForm->addItem(array($this->data['paging'], $myapplicationTable, $this->data['paging'], get_table_header(array($goComboBox, $goButton))));

// append form to widget
$myapplicationWidget->addItem($myapplicationForm);

return $myapplicationWidget;
