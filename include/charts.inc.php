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

function createChooseTree($pageFilter, &$tree){
    $groupsData = $pageFilter->groups;
    $hostsData = $pageFilter->hosts;

    $root = new CLink('root', '#', 'group-choose-menu');
    $root->setAttribute('data-menu', array(
        'serviceid' => 0,
        'name' => 'root',
        'hasDependencies' => true
    ));

    $rootNode = array(
        'id' => 0,
        'parentid' => 0,
        'caption' => $root,
        'trigger' => array(),
        'state' => SPACE
    );
    $index = 0;
    $tree[$index] = $rootNode;
    $index = $index + 1;

    $caption = new CLink('群组', '#', 'group-choose-menu');
    $caption->setAttribute('data-menu', array(
        'serviceid' => 1,
        'name' => '群组',
        'hasDependencies' => true
    ));
    $serviceNode = array(
        'id' => 1,
        'parentid' => 0,
        'caption' => $caption,
        'trigger' => array(),
        'state' => SPACE
    );
    $tree[$index] = $serviceNode;
    $index = $index + 1;

    // add all top level services as children of "root"
    foreach ($groupsData as $groupNode) {
        $captionGroup = new CLink('Haha', '#', 'group-choose-menu');
        $captionGroup->setAttribute('data-menu', array(
            'serviceid' => $groupNode['groupid'],
            'name' => 'Haha',
            'hasDependencies' => true
        ));
        $groupNode = array(
            'id' => $groupNode['groupid'],
            'caption' => $captionGroup,
            'parentid' => 1,
            'state' => _('Normal')
        );
        $tree[$index] = $groupNode;
        $index = $index + 1;
    }

    var_dump($tree);
}
