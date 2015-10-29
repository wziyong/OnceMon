<?php

require_once dirname(__FILE__) . '/include/config.inc.php';
require_once dirname(__FILE__) . '/include/hosts.inc.php';

$page['title'] = _('Configuration  of host groups');
$page['file'] = 'hostgroups.php';
$page['hist_arg'] = array();

require_once dirname(__FILE__) . '/include/page_header.php';

// VAR	TYPE	OPTIONAL	FLAGS	VALIDATION	EXCEPTION
$fields = array(
    'hosts' => array(T_ZBX_INT, O_OPT, P_SYS, DB_ID, null),
    'groups' => array(T_ZBX_INT, O_OPT, P_SYS, DB_ID, null),
    'hostids' => array(T_ZBX_INT, O_OPT, P_SYS, DB_ID, null),
    'groupids' => array(T_ZBX_INT, O_OPT, P_SYS, DB_ID, null),
    // group
    'groupid' => array(T_ZBX_INT, O_OPT, P_SYS, DB_ID, 'isset({form})&&{form}=="update"'),
    'name' => array(T_ZBX_STR, O_OPT, null, NOT_EMPTY, 'isset({save})'),
    'twb_groupid' => array(T_ZBX_INT, O_OPT, P_SYS, DB_ID, null),
    // actions
    'go' => array(T_ZBX_STR, O_OPT, P_SYS | P_ACT, null, null),
    'save' => array(T_ZBX_STR, O_OPT, P_SYS | P_ACT, null, null),
    'clone' => array(T_ZBX_STR, O_OPT, P_SYS | P_ACT, null, null),
    'delete' => array(T_ZBX_STR, O_OPT, P_SYS | P_ACT, null, null),
    'cancel' => array(T_ZBX_STR, O_OPT, P_SYS, null, null),
    // other
    'form' => array(T_ZBX_STR, O_OPT, P_SYS, null, null),
    'form_refresh' => array(T_ZBX_STR, O_OPT, null, null, null)
);
check_fields($fields);
validate_sort_and_sortorder('name', ZBX_SORT_UP);

$_REQUEST['go'] = get_request('go', 'none');

/*
 * Permissions
 */
if (get_request('groupid') && !API::HostGroup()->isWritable(array($_REQUEST['groupid']))) {
    access_deny();
}

/*
 * Actions
 */

/*
 * Display
 */

$data = array(
    'config' => $config,
    'displayNodes' => is_array(get_current_nodeid())
);

$sortfield = getPageSortField('name');
$sortorder = getPageSortOrder();

$groups = API::HostGroup()->get(array(
    'editable' => true,
    'output' => array('groupid'),
    'sortfield' => $sortfield,
    'limit' => $config['search_limit'] + 1
));

$data['paging'] = getPagingLine($groups, array('groupid'));

// get hosts and templates count
$data['groupCounts'] = API::HostGroup()->get(array(
    'groupids' => zbx_objectValues($groups, 'groupid'),
    'selectHosts' => API_OUTPUT_COUNT,
    'selectTemplates' => API_OUTPUT_COUNT,
    'nopermissions' => true
));
$data['groupCounts'] = zbx_toHash($data['groupCounts'], 'groupid');

// get host groups
$data['groups'] = API::HostGroup()->get(array(
    'groupids' => zbx_objectValues($groups, 'groupid'),
    'selectHosts' => array('hostid', 'name', 'status'),
    'selectTemplates' => array('hostid', 'name', 'status'),
    'selectGroupDiscovery' => array('ts_delete'),
    'selectDiscoveryRule' => array('itemid', 'name'),
    'output' => API_OUTPUT_EXTEND,
    'limitSelects' => $config['max_in_table'] + 1
));
order_result($data['groups'], $sortfield, $sortorder);

// nodes
if ($data['displayNodes']) {
    foreach ($data['groups'] as $key => $group) {
        $data['groups'][$key]['nodename'] = get_node_name_by_elid($group['groupid'], true);
    }
}

// render view
$hostgroupView = new CView('custom/cluster.topological', $data);
$hostgroupView->render();
$hostgroupView->show();


require_once dirname(__FILE__) . '/include/page_footer.php';


?>
