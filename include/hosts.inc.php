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


function setHostGroupInternal($groupids, $internal = ZBX_NOT_INTERNAL_GROUP) {
	zbx_value2array($groupids);

	return DBexecute('UPDATE groups SET internal='.$internal.' WHERE '.dbConditionInt('groupid', $groupids));
}

/**
 * Get ipmi auth type label by it's number.
 *
 * @param null|int $type
 *
 * @return array|string
 */
function ipmiAuthTypes($type = null) {
	$types = array(
		IPMI_AUTHTYPE_DEFAULT => _('Default'),
		IPMI_AUTHTYPE_NONE => _('None'),
		IPMI_AUTHTYPE_MD2 => _('MD2'),
		IPMI_AUTHTYPE_MD5 => _('MD5'),
		IPMI_AUTHTYPE_STRAIGHT => _('Straight'),
		IPMI_AUTHTYPE_OEM => _('OEM'),
		IPMI_AUTHTYPE_RMCP_PLUS => _('RMCP+')
	);

	if (is_null($type)) {
		return $types;
	}
	elseif (isset($types[$type])) {
		return $types[$type];
	}
	else {
		return _('Unknown');
	}
}

/**
 * Get ipmi auth privilege label by it's number.
 *
 * @param null|int $type
 *
 * @return array|string
 */
function ipmiPrivileges($type = null) {
	$types = array(
		IPMI_PRIVILEGE_CALLBACK => _('Callback'),
		IPMI_PRIVILEGE_USER => _('User'),
		IPMI_PRIVILEGE_OPERATOR => _('Operator'),
		IPMI_PRIVILEGE_ADMIN => _('Admin'),
		IPMI_PRIVILEGE_OEM => _('OEM')
	);

	if (is_null($type)) {
		return $types;
	}
	elseif (isset($types[$type])) {
		return $types[$type];
	}
	else {
		return _('Unknown');
	}
}

/**
 * Get info about what host inventory fields we have, their numbers and names.
 * Example of usage:
 *      $inventories = getHostInventories();
 *      echo $inventories[1]['db_field']; // host_networks
 *      echo $inventories[1]['title']; // Host networks
 *      echo $inventories[1]['nr']; // 1
 *
 * @param bool $orderedByTitle	whether an array should be ordered by field title, not by number
 *
 * @return array
 */
function getHostInventories($orderedByTitle = false) {
	/*
	 * WARNING! Before modifying this array, make sure changes are synced with C
	 * C analog is located in function DBget_inventory_field() in src/libs/zbxdbhigh/db.c
	 */
	$inventoryFields = array(
		1 => array(
			'nr' => 1,
			'db_field' => 'type',
			'title' => _('Type')
		),
		2 => array(
			'nr' => 2,
			'db_field' => 'type_full',
			'title' => _('Type (Full details)')
		),
		3 => array(
			'nr' => 3,
			'db_field' => 'name',
			'title' => _('Name')
		),
		4 => array(
			'nr' => 4,
			'db_field' => 'alias',
			'title' => _('Alias')
		),
		5 => array(
			'nr' => 5,
			'db_field' => 'os',
			'title' => _('OS')
		),
		6 => array(
			'nr' => 6,
			'db_field' => 'os_full',
			'title' => _('OS (Full details)')
		),
		7 => array(
			'nr' => 7,
			'db_field' => 'os_short',
			'title' => _('OS (Short)')
		),
		8 => array(
			'nr' => 8,
			'db_field' => 'serialno_a',
			'title' => _('Serial number A')
		),
		9 => array(
			'nr' => 9,
			'db_field' => 'serialno_b',
			'title' => _('Serial number B')
		),
		10 => array(
			'nr' => 10,
			'db_field' => 'tag',
			'title' => _('Tag')
		),
		11 => array(
			'nr' => 11,
			'db_field' => 'asset_tag',
			'title' => _('Asset tag')
		),
		12 => array(
			'nr' => 12,
			'db_field' => 'macaddress_a',
			'title' => _('MAC address A')
		),
		13 => array(
			'nr' => 13,
			'db_field' => 'macaddress_b',
			'title' => _('MAC address B')
		),
		14 => array(
			'nr' => 14,
			'db_field' => 'hardware',
			'title' => _('Hardware')
		),
		15 => array(
			'nr' => 15,
			'db_field' => 'hardware_full',
			'title' => _('Hardware (Full details)')
		),
		16 => array(
			'nr' => 16,
			'db_field' => 'software',
			'title' => _('Software')
		),
		17 => array(
			'nr' => 17,
			'db_field' => 'software_full',
			'title' => _('Software (Full details)')
		),
		18 => array(
			'nr' => 18,
			'db_field' => 'software_app_a',
			'title' => _('Software application A')
		),
		19 => array(
			'nr' => 19,
			'db_field' => 'software_app_b',
			'title' => _('Software application B')
		),
		20 => array(
			'nr' => 20,
			'db_field' => 'software_app_c',
			'title' => _('Software application C')
		),
		21 => array(
			'nr' => 21,
			'db_field' => 'software_app_d',
			'title' => _('Software application D')
		),
		22 => array(
			'nr' => 22,
			'db_field' => 'software_app_e',
			'title' => _('Software application E')
		),
		23 => array(
			'nr' => 23,
			'db_field' => 'contact',
			'title' => _('Contact')
		),
		24 => array(
			'nr' => 24,
			'db_field' => 'location',
			'title' => _('Location')
		),
		25 => array(
			'nr' => 25,
			'db_field' => 'location_lat',
			'title' => _('Location latitude')
		),
		26 => array(
			'nr' => 26,
			'db_field' => 'location_lon',
			'title' => _('Location longitude')
		),
		27 => array(
			'nr' => 27,
			'db_field' => 'notes',
			'title' => _('Notes')
		),
		28 => array(
			'nr' => 28,
			'db_field' => 'chassis',
			'title' => _('Chassis')
		),
		29 => array(
			'nr' => 29,
			'db_field' => 'model',
			'title' => _('Model')
		),
		30 => array(
			'nr' => 30,
			'db_field' => 'hw_arch',
			'title' => _('HW architecture')
		),
		31 => array(
			'nr' => 31,
			'db_field' => 'vendor',
			'title' => _('Vendor')
		),
		32 => array(
			'nr' => 32,
			'db_field' => 'contract_number',
			'title' => _('Contract number')
		),
		33 => array(
			'nr' => 33,
			'db_field' => 'installer_name',
			'title' => _('Installer name')
		),
		34 => array(
			'nr' => 34,
			'db_field' => 'deployment_status',
			'title' => _('Deployment status')
		),
		35 => array(
			'nr' => 35,
			'db_field' => 'url_a',
			'title' => _('URL A')
		),
		36 => array(
			'nr' => 36,
			'db_field' => 'url_b',
			'title' => _('URL B')
		),
		37 => array(
			'nr' => 37,
			'db_field' => 'url_c',
			'title' => _('URL C')
		),
		38 => array(
			'nr' => 38,
			'db_field' => 'host_networks',
			'title' => _('Host networks')
		),
		39 => array(
			'nr' => 39,
			'db_field' => 'host_netmask',
			'title' => _('Host subnet mask')
		),
		40 => array(
			'nr' => 40,
			'db_field' => 'host_router',
			'title' => _('Host router')
		),
		41 => array(
			'nr' => 41,
			'db_field' => 'oob_ip',
			'title' => _('OOB IP address')
		),
		42 => array(
			'nr' => 42,
			'db_field' => 'oob_netmask',
			'title' => _('OOB subnet mask')
		),
		43 => array(
			'nr' => 43,
			'db_field' => 'oob_router',
			'title' => _('OOB router')
		),
		44 => array(
			'nr' => 44,
			'db_field' => 'date_hw_purchase',
			'title' => _('Date HW purchased')
		),
		45 => array(
			'nr' => 45,
			'db_field' => 'date_hw_install',
			'title' => _('Date HW installed')
		),
		46 => array(
			'nr' => 46,
			'db_field' => 'date_hw_expiry',
			'title' => _('Date HW maintenance expires')
		),
		47 => array(
			'nr' => 47,
			'db_field' => 'date_hw_decomm',
			'title' => _('Date HW decommissioned')
		),
		48 => array(
			'nr' => 48,
			'db_field' => 'site_address_a',
			'title' => _('Site address A')
		),
		49 => array(
			'nr' => 49,
			'db_field' => 'site_address_b',
			'title' => _('Site address B')
		),
		50 => array(
			'nr' => 50,
			'db_field' => 'site_address_c',
			'title' => _('Site address C')
		),
		51 => array(
			'nr' => 51,
			'db_field' => 'site_city',
			'title' => _('Site city')
		),
		52 => array(
			'nr' => 52,
			'db_field' => 'site_state',
			'title' => _('Site state / province')
		),
		53 => array(
			'nr' => 53,
			'db_field' => 'site_country',
			'title' => _('Site country')
		),
		54 => array(
			'nr' => 54,
			'db_field' => 'site_zip',
			'title' => _('Site ZIP / postal')
		),
		55 => array(
			'nr' => 55,
			'db_field' => 'site_rack',
			'title' => _('Site rack location')
		),
		56 => array(
			'nr' => 56,
			'db_field' => 'site_notes',
			'title' => _('Site notes')
		),
		57 => array(
			'nr' => 57,
			'db_field' => 'poc_1_name',
			'title' => _('Primary POC name')
		),
		58 => array(
			'nr' => 58,
			'db_field' => 'poc_1_email',
			'title' => _('Primary POC email')
		),
		59 => array(
			'nr' => 59,
			'db_field' => 'poc_1_phone_a',
			'title' => _('Primary POC phone A')
		),
		60 => array(
			'nr' => 60,
			'db_field' => 'poc_1_phone_b',
			'title' => _('Primary POC phone B')
		),
		61 => array(
			'nr' => 61,
			'db_field' => 'poc_1_cell',
			'title' => _('Primary POC cell')
		),
		62 => array(
			'nr' => 62,
			'db_field' => 'poc_1_screen',
			'title' => _('Primary POC screen name')
		),
		63 => array(
			'nr' => 63,
			'db_field' => 'poc_1_notes',
			'title' => _('Primary POC notes')
		),
		64 => array(
			'nr' => 64,
			'db_field' => 'poc_2_name',
			'title' => _('Secondary POC name')
		),
		65 => array(
			'nr' => 65,
			'db_field' => 'poc_2_email',
			'title' => _('Secondary POC email')
		),
		66 => array(
			'nr' => 66,
			'db_field' => 'poc_2_phone_a',
			'title' => _('Secondary POC phone A')
		),
		67 => array(
			'nr' => 67,
			'db_field' => 'poc_2_phone_b',
			'title' => _('Secondary POC phone B')
		),
		68 => array(
			'nr' => 68,
			'db_field' => 'poc_2_cell',
			'title' => _('Secondary POC cell')
		),
		69 => array(
			'nr' => 69,
			'db_field' => 'poc_2_screen',
			'title' => _('Secondary POC screen name')
		),
		70 => array(
			'nr' => 70,
			'db_field' => 'poc_2_notes',
			'title' => _('Secondary POC notes')
		)
	);

	// array is ordered by number by default, should we change that and order by title?
	if ($orderedByTitle) {
		function sortInventoriesByTitle($a, $b) {
			return strcmp($a['title'], $b['title']);
		}
		uasort($inventoryFields, 'sortInventoriesByTitle');
	}

	return $inventoryFields;
}

function hostInterfaceTypeNumToName($type) {
	switch ($type) {
		case INTERFACE_TYPE_AGENT:
			$name = _('agent');
			break;
		case INTERFACE_TYPE_SNMP:
			$name = _('SNMP');
			break;
		case INTERFACE_TYPE_JMX:
			$name = _('JMX');
			break;
		case INTERFACE_TYPE_IPMI:
			$name = _('IPMI');
			break;
		case INTERFACE_TYPE_ONCEAGENT:
			$name = _('管理Agent接口');
			break;
		default:
			throw new Exception(_('Unknown interface type.'));
	}

	return $name;
}

function get_hostgroup_by_groupid($groupid) {
	$groups = DBfetch(DBselect('SELECT g.* FROM groups g WHERE g.groupid='.zbx_dbstr($groupid)));

	if ($groups) {
		return $groups;
	}

	error(_s('No host groups with groupid "%s".', $groupid));

	return false;
}

function get_host_by_itemid($itemids) {
	$res_array = is_array($itemids);
	zbx_value2array($itemids);
	$result = false;
	$hosts = array();

	$db_hostsItems = DBselect(
		'SELECT i.itemid,h.*'.
		' FROM hosts h,items i'.
		' WHERE i.hostid=h.hostid'.
			' AND '.dbConditionInt('i.itemid', $itemids)
	);
	while ($hostItem = DBfetch($db_hostsItems)) {
		$result = true;
		$hosts[$hostItem['itemid']] = $hostItem;
	}

	if (!$res_array) {
		foreach ($hosts as $itemid => $host) {
			$result = $host;
		}
	}
	elseif ($result) {
		$result = $hosts;
		unset($hosts);
	}

	return $result;
}

function get_host_by_hostid($hostid, $no_error_message = 0) {
	$row = DBfetch(DBselect('SELECT h.* FROM hosts h WHERE h.hostid='.zbx_dbstr($hostid)));

	if ($row) {
		return $row;
	}

	if ($no_error_message == 0) {
		error(_s('No host with hostid "%s".', $hostid));
	}

	return false;
}

function get_hosts_by_templateid($templateids) {
	zbx_value2array($templateids);

	return DBselect(
		'SELECT h.*'.
		' FROM hosts h,hosts_templates ht'.
		' WHERE h.hostid=ht.hostid'.
			' AND '.dbConditionInt('ht.templateid', $templateids)
	);
}

function updateHostStatus($hostids, $status) {
	zbx_value2array($hostids);

	$hostIds = array();
	$oldStatus = ($status == HOST_STATUS_MONITORED ? HOST_STATUS_NOT_MONITORED : HOST_STATUS_MONITORED);

	$db_hosts = DBselect(
		'SELECT h.hostid,h.host,h.status'.
		' FROM hosts h'.
		' WHERE '.dbConditionInt('h.hostid', $hostids).
			' AND h.status='.$oldStatus
	);
	while ($host = DBfetch($db_hosts)) {
		$hostIds[] = $host['hostid'];

		$host_new = $host;
		$host_new['status'] = $status;
		add_audit_ext(AUDIT_ACTION_UPDATE, AUDIT_RESOURCE_HOST, $host['hostid'], $host['host'], 'hosts', $host, $host_new);
		info(_('Updated status of host').' "'.$host['host'].'"');
	}

	return DB::update('hosts', array(
		'values' => array('status' => $status),
		'where' => array('hostid' => $hostIds)
	));
}

function get_application_by_applicationid($applicationid, $no_error_message = 0) {
	$row = DBfetch(DBselect('SELECT a.* FROM applications a WHERE a.applicationid='.zbx_dbstr($applicationid)));

	if ($row) {
		return $row;
	}

	if ($no_error_message == 0) {
		error(_s('No application with ID "%1$s".', $applicationid));
	}

	return false;
}

/**
 * Returns the farthest application ancestor for each given application.
 *
 * @param array $applicationIds
 * @param array $templateApplicationIds		array with parent application IDs as keys and arrays of child application
 * 											IDs as values
 *
 * @return array	an array with child IDs as keys and arrays of ancestor IDs as values
 */
function getApplicationSourceParentIds(array $applicationIds, array $templateApplicationIds = array()) {
	$query = DBSelect(
		'SELECT at.applicationid,at.templateid'.
		' FROM application_template at'.
		' WHERE '.dbConditionInt('at.applicationid', $applicationIds)
	);

	$applicationIds = array();
	$unsetApplicationIds = array();
	while ($applicationTemplate = DBfetch($query)) {
		// check if we already have an application inherited from the current application
		// if we do - copy all of its child applications to the parent template
		if (isset($templateApplicationIds[$applicationTemplate['applicationid']])) {
			$templateApplicationIds[$applicationTemplate['templateid']] = $templateApplicationIds[$applicationTemplate['applicationid']];
			$unsetApplicationIds[$applicationTemplate['applicationid']] = $applicationTemplate['applicationid'];
		}
		// if no - just add the application
		else {
			$templateApplicationIds[$applicationTemplate['templateid']][] = $applicationTemplate['applicationid'];
		}
		$applicationIds[$applicationTemplate['applicationid']] = $applicationTemplate['templateid'];
	}

	// unset children of all applications that we found a new parent for
	foreach ($unsetApplicationIds as $applicationId) {
		unset($templateApplicationIds[$applicationId]);
	}

	// continue while we still have new applications to check
	if ($applicationIds) {
		return getApplicationSourceParentIds($applicationIds, $templateApplicationIds);
	}
	else {
		// return an inverse hash with application IDs as keys and arrays of parent application IDs as values
		$result = array();
		foreach ($templateApplicationIds as $templateId => $applicationIds) {
			foreach ($applicationIds as $applicationId) {
				$result[$applicationId][] = $templateId;
			}
		}

		return $result;
	}
}

/**
 * Returns the farthest host prototype ancestor for each given host prototype.
 *
 * @param array $hostPrototypeIds
 * @param array $templateHostPrototypeIds	array with parent host prototype IDs as keys and arrays of child host
 * 											prototype IDs as values
 *
 * @return array	an array of child ID - ancestor ID pairs
 */
function getHostPrototypeSourceParentIds(array $hostPrototypeIds, array $templateHostPrototypeIds = array()) {
	$query = DBSelect(
		'SELECT h.hostid,h.templateid'.
		' FROM hosts h'.
		' WHERE '.dbConditionInt('h.hostid', $hostPrototypeIds).
			' AND h.templateid>0'
	);

	$hostPrototypeIds = array();
	while ($hostPrototype = DBfetch($query)) {
		// check if we already have host prototype inherited from the current host prototype
		// if we do - move all of its child prototypes to the parent template
		if (isset($templateHostPrototypeIds[$hostPrototype['hostid']])) {
			$templateHostPrototypeIds[$hostPrototype['templateid']] = $templateHostPrototypeIds[$hostPrototype['hostid']];
			unset($templateHostPrototypeIds[$hostPrototype['hostid']]);
		}
		// if no - just add the prototype
		else {
			$templateHostPrototypeIds[$hostPrototype['templateid']][] = $hostPrototype['hostid'];
			$hostPrototypeIds[] = $hostPrototype['templateid'];
		}
	}

	// continue while we still have new host prototypes to check
	if ($hostPrototypeIds) {
		return getHostPrototypeSourceParentIds($hostPrototypeIds, $templateHostPrototypeIds);
	}
	else {
		// return an inverse hash with prototype IDs as keys and parent prototype IDs as values
		$result = array();
		foreach ($templateHostPrototypeIds as $templateId => $hostIds) {
			foreach ($hostIds as $hostId) {
				$result[$hostId] = $templateId;
			}
		}

		return $result;
	}
}

/**
 * Check collisions between templates.
 * $param int|array $templateid_list
 */
function validate_templates($templateid_list) {
	if (is_numeric($templateid_list)) {
		return true;
	}
	if (!is_array($templateid_list)) {
		return false;
	}
	if (count($templateid_list) < 2) {
		return true;
	}

	$result = true;

	$res = DBselect(
		'SELECT key_,COUNT(*) AS cnt'.
		' FROM items'.
		' WHERE '.dbConditionInt('hostid', $templateid_list).
		' GROUP BY key_'.
		' ORDER BY cnt DESC'
	);
	while ($db_cnt = DBfetch($res)) {
		if ($db_cnt['cnt'] > 1) {
			$result &= false;
			error(_s('Template with item key "%1$s" already linked to host.', htmlspecialchars($db_cnt['key_'])));
		}
	}

	return $result;
}

/**
 * Get host ids of hosts which $groupids can be unlinked from.
 * if $hostids is passed, function will check only these hosts.
 *
 * @param array $groupids
 * @param array $hostids
 *
 * @return array
 */
function getUnlinkableHosts($groupids, $hostids = null) {
	zbx_value2array($groupids);
	zbx_value2array($hostids);

	$unlinkableHostIds = array();

	$sql_where = '';
	if ($hostids !== null) {
		$sql_where = ' AND '.dbConditionInt('hg.hostid', $hostids);
	}

	$result = DBselect(
		'SELECT hg.hostid,COUNT(hg.groupid) AS grp_count'.
		' FROM hosts_groups hg'.
		' WHERE '.dbConditionInt('hg.groupid', $groupids, true).
				$sql_where.
		' GROUP BY hg.hostid'.
		' HAVING COUNT(hg.groupid)>0'
	);
	while ($row = DBfetch($result)) {
		$unlinkableHostIds[] = $row['hostid'];
	}

	return $unlinkableHostIds;
}

function getDeletableHostGroups($groupids = null) {
	$deletable_groupids = array();

	zbx_value2array($groupids);
	$hostids = getUnlinkableHosts($groupids);

	$sql_where = '';
	if (!is_null($groupids)) {
		$sql_where .= ' AND '.dbConditionInt('g.groupid', $groupids);
	}

	$db_groups = DBselect(
		'SELECT DISTINCT g.groupid'.
		' FROM groups g'.
		' WHERE g.internal='.ZBX_NOT_INTERNAL_GROUP.
			$sql_where.
			' AND NOT EXISTS ('.
				'SELECT NULL'.
				' FROM hosts_groups hg'.
				' WHERE g.groupid=hg.groupid'.
					(!empty($hostids) ? ' AND '.dbConditionInt('hg.hostid', $hostids, true) : '').
			')'
	);
	while ($group = DBfetch($db_groups)) {
		$deletable_groupids[$group['groupid']] = $group['groupid'];
	}

	return $deletable_groupids;
}

function isTemplate($hostId) {
	$dbHost = DBfetch(DBselect('SELECT h.status FROM hosts h WHERE h.hostid='.zbx_dbstr($hostId)));

	return ($dbHost && $dbHost['status'] == HOST_STATUS_TEMPLATE);
}

function isTemplateInHost($hosts) {
	if ($hosts) {
		$hosts = zbx_toArray($hosts);

		foreach ($hosts as $host) {
			if (!empty($host['templateid'])) {
				return $host['templateid'];
			}
		}
	}

	return 0;
}


//add by wziyong for 同步配置到服务器上；
//同步负载均衡器配置
function synchronizeLbsServer($host) {
		foreach($host['interfaces'] as $interfaceX)
		{
			if($interfaceX['type'] == '5' && $interfaceX['main'] == '1')
			{
				$agent_ip = $interfaceX['ip'];
				$agent_port = $interfaceX['port'];
				break;
			}
		}

	 if(empty($agent_ip) ||empty($agent_port))
	 {
		return array("result"=>false,'message'=>'负载均衡器管理agent的ip或端口为空');
	 }

		$appcfg = $host['lbscfg'];
		$configs = array();
		foreach($appcfg as $cfg)
		{
			$configs[$cfg['name']] = $cfg['value'];
		}

		$cfg_servers = '';
		$tmp = DBselect('select h.hostid,h.server_type from hosts_groups g,hosts h  where g.hostid = h.hostid and parentId ='.$host['hostid']);
		while ($hosttmp = DBfetch($tmp)) {
			$hostidx = $hosttmp['hostid'];
			$server_typex = $hosttmp['server_type'];

			$hostX = API::Host()->get(array(
				'hostids' => $hostidx,
				'selectInterfaces' => API_OUTPUT_EXTEND,
				'selectCfgs' => API_OUTPUT_EXTEND,
				'output' => API_OUTPUT_EXTEND
			));

			foreach($hostX[0]['interfaces'] as $interfaceX)
			{
				if($interfaceX['type'] == '5' && $interfaceX['main'] == '1')
				{
					$iptmp = $interfaceX['ip'];
				}
			}

			if($server_typex == HOST_SERVER_TYPE_APP)
			{
				$porttmp = $hostX[0]['hostservercfgs']['app_http_port'];
			}
			if($server_typex == HOST_SERVER_TYPE_LBS)
			{
				$porttmp = $hostX[0]['hostservercfgs']['lbs_listen_port'];
			}

			if(!empty($iptmp) && !empty($porttmp))
			{
				$cfg_servers = $cfg_servers.$iptmp.':'.$porttmp.',';
			}
		}

		$agent_cfg_msg="{servertype:'nginx',optype:'10',args:{lbs_log_level:'".$configs['lbs_log_level']."',lbs_log_path:'".$configs['lbs_log_path']."',lbs_listen_port:'".$configs['lbs_listen_port']."',lbs_upstream_type:'".$configs['lbs_upstream_type']."',lbs_upstream_servers:'".substr($cfg_servers,0,-1)."'}}";
		$response_result = AgentManager::send($agent_ip,$agent_port,$agent_cfg_msg);
		if(null != $response_result && $response_result['result'] == 'true')
		{
			return array("result"=>true,'message'=>'负载均衡器配置同步成功');
		}

	return array("result"=>false,'message'=>$response_result['message']);
}

function synchronizeParent($hostId) {
    if(!empty($hostId))
    {
        $dbHost = API::Host()->get(array(
            'hostids' => $hostId,
            'selectInterfaces' => API_OUTPUT_EXTEND,
            'selectCfgs' => API_OUTPUT_EXTEND,
            'output' => API_OUTPUT_EXTEND
        ));

        foreach($dbHost[0]['interfaces'] as $interfaceX)
        {
            if($interfaceX['type'] == '5' && $interfaceX['main'] == '1')
            {
                $agent_ip = $interfaceX['ip'];
                $agent_port = $interfaceX['port'];
                break;
            }
        }

		if(empty($agent_ip) ||empty($agent_port))
		{
			return array("result"=>false,'message'=>'负载均衡器管理agent的ip或端口为空');
		}

        $appcfg = $dbHost[0]['hostservercfgs'];
        $configs = array();
        foreach($appcfg as $key=>$cfg)
        {
            $configs[$key] = $cfg;
        }

        $cfg_servers = '';
        $tmp = DBselect('select h.hostid,h.server_type from hosts_groups g,hosts h  where g.hostid = h.hostid and parentId ='.$hostId);
        while ($hosttmp = DBfetch($tmp)) {
            $hostidx = $hosttmp['hostid'];
            $server_typex = $hosttmp['server_type'];

            $hostX = API::Host()->get(array(
                'hostids' => $hostidx,
                'selectInterfaces' => API_OUTPUT_EXTEND,
                'selectCfgs' => API_OUTPUT_EXTEND,
                'output' => API_OUTPUT_EXTEND
            ));

            foreach($hostX[0]['interfaces'] as $interfaceX)
            {
                if($interfaceX['type'] == '5' && $interfaceX['main'] == '1')
                {
                    $iptmp = $interfaceX['ip'];
                }
            }

            if($server_typex == HOST_SERVER_TYPE_APP)
            {
                $porttmp = $hostX[0]['hostservercfgs']['app_http_port'];
            }
            if($server_typex == HOST_SERVER_TYPE_LBS)
            {
                $porttmp = $hostX[0]['hostservercfgs']['lbs_listen_port'];
            }

            if(!empty($iptmp) && !empty($porttmp))
            {
                $cfg_servers = $cfg_servers.$iptmp.':'.$porttmp.',';
            }
        }

        $agent_cfg_msg="{servertype:'nginx',optype:'10',args:{lbs_log_level:'".$configs['lbs_log_level']."',lbs_log_path:'".$configs['lbs_log_path']."',lbs_listen_port:'".$configs['lbs_listen_port']."',lbs_upstream_type:'".$configs['lbs_upstream_type']."',lbs_upstream_servers:'".substr($cfg_servers,0,-1)."'}}";
        $response_result = AgentManager::send($agent_ip,$agent_port,$agent_cfg_msg);
        if(null!= $response_result && $response_result['result'] == 'true')
        {
			return true;
        }
    }

	return false;
}


//同步应用服务器；先同步配置，后同步应用列表；然后同步父节点；
function synchronizeAppServer($host) {
    foreach($host['interfaces'] as $interface)
    {
        if($interface['type'] == '5' && $interface['main'] == '1' )
        {
            $agent_ip = $interface['ip'];
            $agent_port = $interface['port'];
            break;
        }
    }

    if(empty($agent_ip) || empty($agent_port))
    {
        return array("result"=>false,'message'=>'管理agent的ip或端口为空');
    }

    $appcfg = $host['appcfg'];
    $configs = array();
    foreach($appcfg as $cfg)
    {
        $configs[$cfg['name']] = $cfg['value'];
    }
    $agent_cfg_msg="{servertype:'tomcat',optype:'10',args:{app_http_port:'".$configs['app_http_port']."',app_log_level:'".$configs['app_log_level']."'}}";

    $response_result = AgentManager::send($agent_ip,$agent_port,$agent_cfg_msg);
    if(null!= $response_result && $response_result['result'] == 'true')//同步成功，则部署应用
    {
        $myapplicationTmp = DBselect('select * from t_custom_myapplication t1,t_custom_hostapps t2 where t1.applicationid = t2.applicationid and t2.hostid ='.$host['hostid']);
        $myapplications = array();
        while ($myapplication = DBfetch($myapplicationTmp)) {
            $myapplications[] = $myapplication['filename'];
        }
        global  $FTP;
        $agent_app_msg="{servertype:'tomcat',optype:'12',args:{app_ftp_ip:'".$FTP['FTP_HOST']."',app_ftp_port:'".$FTP['FTP_PORT']."',app_ftp_name:'".$FTP['FTP_USER']."',app_ftp_password:'".$FTP['FTP_PASS']."',app_file_name:'".implode(',',$myapplications)."'}}";
        $response_result2 = AgentManager::send($agent_ip,$agent_port,$agent_app_msg);
        if(null!= $response_result2 && $response_result2['result'] == 'false')
        {
            return array("result"=>false,'message'=>'部署应用失败'.$response_result2['message']);
        }
        else{
            if(synchronizeParent($host['parentid']))
			{
				return array("result"=>true,'message'=>'success');
			}
			else
			{
				return array("result"=>false,'message'=>'同步所属负载均衡器失败');
			}
        }
    }
    else{
        return array("result"=>false,'message'=>'同步配置到远程服务器失败'.$response_result['message']);
    }

    return array("result"=>true,'message'=>'success');
}

function synchronize($host)
{
    if(null == $host)
    {
        return array("result"=>false,'message'=>'host is null');;
    }

	$result = null;

    switch ($host['server_type']){
        case HOST_SERVER_TYPE_LBS:
			$result = synchronizeLbsServer($host);
            break;
        case HOST_SERVER_TYPE_APP:
			$result =  synchronizeAppServer($host);
            break;
        case HOST_SERVER_TYPE_CACHE:
			$result =  array("result"=>true,'message'=>'success');
            break;
        default:
			$result =  array("result"=>false,'message'=>'server_type is error');;
    }

	if($result['result'] == true)
	{
		if(!DB::update('hosts', array('values' => array('manage_status' => 1),'where' => array('hostid' => $host['hostid']))))
		{
			$result = array("result"=>false,'message'=>'更新同步状态失败');;
		}
	}

	return $result;
}

function synchronizeBAK($hosts) {
	zbx_value2array($hosts);
	$hostIds = array();
	$errorCount = 0;
	global  $FTP;

	foreach($hosts as $host)
	{
		$tmp = DBselect('select * from t_custom_hostconfig where hostid = '.$host['hostid']);
		$configs = array();

		while ($config = DBfetch($tmp)) {
			$configs[$config['name']] = $config['value'];
		}

		if(HOST_SERVER_TYPE_APP == $host['server_type']) //如果是应用服务器
		{
			$agent_ip = $configs['agent_ip'];
			$agent_port = $configs['agent_port'];
			$agent_cfg_msg="{servertype:'tomcat',optype:'10',args:{app_http_port:'".$configs['app_http_port']."',app_log_level:'".$configs['app_log_level']."'}}";

			$response_result = AgentManager::send($agent_ip,$agent_port,$agent_cfg_msg);
			if($response_result['result'])//同步成功，则部署应用
			{
				$myapplicationTmp = DBselect('select * from t_custom_myapplication t1,t_custom_hostapps t2 where t1.applicationid = t2.applicationid and t2.hostid ='.$host['hostid']);
				$myapplications = array();
				while ($myapplication = DBfetch($myapplicationTmp)) {
					$myapplications[] = $myapplication['filename'];
				}
				$agent_app_msg="{servertype:'tomcat',optype:'12',args:{app_ftp_ip:'".$FTP['FTP_HOST']."',app_ftp_port:'".$FTP['FTP_PORT']."',app_ftp_name:'".$FTP['FTP_USER']."',app_ftp_password:'".$FTP['FTP_PASS']."',app_file_name:'".implode(',',$myapplications)."'}}";
				$response_result2 = AgentManager::send($agent_ip,$agent_port,$agent_app_msg);
				if($response_result2['result'])
				{
					//修改状态
					//如果失败，失败计数；
					if(!DB::update('hosts', array('values' => array('manage_status' => 2),'where' => array('hostid' => $host['hostid']))))
					{
						$errorCount ++;
					}
				}
				else
				{
					$errorCount ++;
				}
			}
			else //
			{
				$errorCount ++;
			}
		}

		//TODO 同步nginx配置
		if(HOST_SERVER_TYPE_LBS == $host['server_type'])
		{
			$agent_ip = $configs['agent_ip'];
			$agent_port = $configs['agent_port'];

			$cfg_servers = '';
			$tmp = DBselect('select h.hostid,h.server_type from hosts_groups g,hosts h  where g.hostid = h.hostid and parentId ='.$host['hostid']);
			while ($hosttmp = DBfetch($tmp)) {
				$hostidx = $hosttmp['hostid'];
				$server_typex = $hosttmp['server_type'];
				$cfgTmps = DBselect("select name,value from t_custom_hostconfig where name in('agent_ip','lbs_listen_port','app_http_port') and  hostid = ".$hosttmp['hostid']);
				$iptmp = '';
				$porttmp='';
				while($cfgTms = DBfetch($cfgTmps))
				{
					if('agent_ip' === $cfgTms['name'])
					{
						$iptmp =  $cfgTms['value'];
					}

					if('lbs_listen_port' === $cfgTms['name'] || 'app_http_port' === $cfgTms['name'] )
					{
						$porttmp =  $cfgTms['value'];
					}
				}
				$cfg_servers = $cfg_servers.$iptmp.':'.$porttmp.',';
			}

			$agent_cfg_msg="{servertype:'nginx',optype:'10',args:{lbs_log_level:'".$configs['lbs_log_level']."',lbs_log_path:'".$configs['lbs_log_path']."',lbs_listen_port:'".$configs['lbs_listen_port']."',lbs_upstream_type:'".$configs['lbs_upstream_type']."',lbs_upstream_servers:'".substr($cfg_servers,0,-1)."'}}";
			$response_result = AgentManager::send($agent_ip,$agent_port,$agent_cfg_msg);
			if($response_result['result'])
			{
				if(!DB::update('hosts', array('values' => array('manage_status' => 2),'where' => array('hostid' => $host['hostid']))))
				{
					$errorCount ++;
				}
			}
			else{
				$errorCount++;
			}
		}
	}

	return $errorCount;



//	$db_hosts = DBselect(
//		'SELECT h.hostid,h.host,h.status'.
//		' FROM hosts h'.
//		' WHERE '.dbConditionInt('h.hostid', $hostids).
//		' AND h.status='.$oldStatus
//	);
//	while ($host = DBfetch($db_hosts)) {
//		$hostIds[] = $host['hostid'];
//
//		$host_new = $host;
//		$host_new['status'] = $status;
//		add_audit_ext(AUDIT_ACTION_UPDATE, AUDIT_RESOURCE_HOST, $host['hostid'], $host['host'], 'hosts', $host, $host_new);
//		info(_('Updated status of host').' "'.$host['host'].'"');
//	}

//	return DB::update('hosts', array(
//		'values' => array('status' => $status),
//		'where' => array('hostid' => $hostIds)
//	));

}


