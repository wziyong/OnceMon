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


/**
 * Class containing methods for operations with host configuration.
 *
 * @package API
 */
class CHostServerCfg extends CZBXAPI {

	protected $tableName = 't_custom_hostconfig';
	protected $tableAlias = 'hm';
	protected $sortColumns = array('macro');

	/**
	 * Add new host server configuration.
	 *
	 * @param array $hostMacros an array of host server configuration.
	 *
	 * @return array
	 */
	public function create(array $hostCfgs) {
		$hostCfgs = zbx_toArray($hostCfgs);
		//$this->validateCreate($hostMacros);
		$hostCfgids = DB::insert('t_custom_hostconfig', $hostCfgs);
		return array('hostcfgids' => $hostCfgids);
	}


	public function get($options = array()) {
		$result = array();
		if(empty($options['hostids']))
		{
			return $result;
		}
		$hostids = '('.implode(',',$options['hostids']).')';
		$dbApps = DBselect('SELECT t.hostconfigid,t.hostid,t.name,t.value FROM t_custom_hostconfig t WHERE t.hostid in '.$hostids);
		while ($dbApp = DBfetch($dbApps)) {
			$result[$dbApp['hostid']][$dbApp['name']] = $dbApp['value'];
		}
		//返回key/value形式；
		return $result;
	}



}
