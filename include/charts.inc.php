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
    $index = 0;

    // add "群组"
    $caption = new CLink('群组', '#', 'group-choose-menu');
    $caption->setAttribute('data-menu', array(
        'name' => '群组'
    ));
    $rootNode = array(
        'id' => (string)$index,
        'parentid' => $index,
        'caption' => $caption,
        'state' => SPACE
    );
    $tree[$index] = $rootNode;
    $index = $index + 1;

    // add all groups as children of "群组"
    foreach ($groupsData as $groupData) {
        $captionGroup = new CLink($groupData['name'], '#', 'group-choose-menu');
        $captionGroup->setAttribute('data-menu', array(
            'name' => $groupData['name']
        ));
        $groupNode = array(
            'id' => (string)$index,
            'caption' => $captionGroup,
            'parentid' => '0',
            'state' => ''
        );
        $tree[$index] = $groupNode;
        $index = $index + 1;

        // add hosts of group
        $hostsData = $pageFilter->getHostByGroup($groupData['groupid']);
        foreach ($hostsData as $hostData) {
            $captionHost = new CLink($hostData['name'], 'charts.php?form_refresh=1&fullscreen=0&'.
                'groupid='.$groupData['groupid'].'&hostid='.$hostData['hostid']);
            $captionHost->setAttribute('data-menu', array(
                'name' => $hostData['name']
            ));
            switch ($hostData['available']) {
                case HOST_AVAILABLE_TRUE:
                    $stateSpan = new CSpan(_('Normal'), 'off');
                    break;
                case HOST_AVAILABLE_FALSE:
                    $stateSpan = new CSpan(_('Problem'), 'on');
                    break;
                case HOST_AVAILABLE_UNKNOWN:
                    $stateSpan = new CSpan(_('Unknown'), 'on');
                    break;
            }
            $hostNode = array(
                'id' => (string)$index,
                'caption' => $captionHost,
                'parentid' => $groupNode['id'],
                'state' => $stateSpan
            );
            $tree[$index] = $hostNode;
            $index = $index + 1;
        }
    }
}
