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


$myApplicationWidget = new CWidget();
$myApplicationWidget->addPageHeader(_('配置应用'));

// create form
$myApplicationForm = new CForm('post', null, 'multipart/form-data');
$myApplicationForm->setName('myApplicationForm');
$myApplicationForm->addVar('form', $this->data['form']);
$myApplicationForm->addVar('form_refresh', $this->data['form_refresh'] + 1);
$myApplicationForm->addVar('myapplicationid', $this->data['myapplicationid']);

// create form list
$myApplicationFormList = new CFormList('myApplicationFormList');
$nameTextBox = new CTextBox('name', $this->data['name'], ZBX_TEXTBOX_STANDARD_SIZE, 'no', 100);
$nameTextBox->attr('autofocus', 'autofocus');
$myApplicationFormList->addRow(_('Name'), $nameTextBox);

$file =  new CFile('import_file');
$file->attr('style','width:318px');
$myApplicationFormList->addRow(_('安装包'),$file);

$commentTextArea = new CTextArea('comment',
	$this->data['comment'],
	array('rows' => ZBX_TEXTAREA_STANDARD_ROWS, 'width' => ZBX_TEXTAREA_STANDARD_WIDTH)
);
$myApplicationFormList->addRow(_('Comment'), $commentTextArea);

// append form list to tab
$mediaTypeTab = new CTabView();
$mediaTypeTab->addTab('mediaTypeTab', _('应用配置'), $myApplicationFormList);

// append tab to form
$myApplicationForm->addItem($mediaTypeTab);

// append buttons to form
if (empty($this->data['mediatypeid'])) {
	$myApplicationForm->addItem(makeFormFooter(new CSubmit('save', _('Save')), array(new CButtonCancel(url_param('config')))));
}
else {
	$myApplicationForm->addItem(makeFormFooter(new CSubmit('save', _('Save')), array(new CButtonDelete(_('Delete selected media type?'), url_param('form').url_param('mediatypeid').url_param('config')), new CButtonCancel(url_param('config')))));
}


// append form to widget
$myApplicationWidget->addItem($myApplicationForm);


return $myApplicationWidget;
