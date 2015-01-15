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


$chartsWidget = new CWidget('hat_charts');

$leftForm = new CForm('get');
$leftForm->addVar('fullscreen', $this->data['fullscreen']);
$leftForm->addItem(array(_('Group').SPACE, $this->data['pageFilter']->getGroupsCB(true)));
$leftForm->addItem(array(SPACE._('Host').SPACE, $this->data['pageFilter']->getHostsCB(true)));
$leftForm->addItem(array(SPACE._('Graph').SPACE, $this->data['pageFilter']->getGraphsCB(true)));

if ($this->data['graphid']) {
	$chartsWidget->addPageHeader(_('GRAPHS'), array(
		get_icon('favourite', array('fav' => 'web.favorite.graphids', 'elname' => 'graphid', 'elid' => $this->data['graphid'])),
		SPACE,
		get_icon('reset', array('id' => $this->data['graphid'])),
		SPACE,
		get_icon('fullscreen', array('fullscreen' => $this->data['fullscreen']))
	));
}
else {
	$chartsWidget->addPageHeader(_('GRAPHS'), array(get_icon('fullscreen', array('fullscreen' => $this->data['fullscreen']))));
}

$chartTable = new CTable(null, 'maxwidth');

/*
 * Columns
 */
$columns = array_fill(0, 2, array());
$graph_title = isset($this->data['pageFilter']->graphs[$this->data['graphid']]['name'])
		? $this->data['pageFilter']->graphs[$this->data['graphid']]['name']
		: null;
$left_grph = new CUIWidget('hat_choose', $leftForm);
$left_grph->setHeader('选择图表');
$left_grph->setFooter(null, true);
$columns[0][0] = $left_grph;

$rightForm = new CForm('get');
$rightForm->addItem(new CDiv(null, null, 'scrollbar_cntr'));
if (!empty($this->data['graphid'])) {
	// append chart to widget
	$screen = CScreenBuilder::getScreen(array(
		'resourcetype' => SCREEN_RESOURCE_CHART,
		'graphid' => $this->data['graphid'],
		'profileIdx' => 'web.screens',
		'profileIdx2' => $this->data['graphid']
	));

	$rightForm->addItem($screen->get());
	$right_grph = new CUIWidget('hat_favgrph', $rightForm);
	$right_grph->setHeader($graph_title);
	$right_grph->setFooter(null, true);
	$columns[1][0] = $right_grph;

	CScreenBuilder::insertScreenStandardJs(array(
		'timeline' => $screen->timeline,
		'profileIdx' => $screen->profileIdx
	));
}
else {
	$screen = new CScreenBuilder();
	CScreenBuilder::insertScreenStandardJs(array(
		'timeline' => $screen->timeline
	));

	$columns[1][1] = new CTableInfo(_('No graphs found.'));
}

$chartTable->addRow(array(new CDiv($columns[0], 'column'), new CDiv($columns[1], 'column')), 'top');

$chartsWidget->addItem($chartTable);

return $chartsWidget;
