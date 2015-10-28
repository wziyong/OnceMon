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
 * Class containing methods for applications
 *
 * @package API
 */
class CMyApplication extends CZBXAPI
{

    protected $tableName = 't_custom_myapplication';
    protected $tableAlias = 'myapp';
    protected $sortColumns = array('applicationid');


    public function create($myapplications)
    {
        $myapplications = zbx_toArray($myapplications);
//		foreach ($myapplications as $mediatype) {
//			$mediatpeExist = $this->get(array(
//				'filter' => array('description' => $mediatype['description']),
//				'output' => API_OUTPUT_EXTEND
//			));
//			if (!empty($mediatypeExist)) {
//				self::e//xception(ZBX_API_ERROR_PARAMETERS, _s('Media type "%s" already exists.', $mediatypeExist[0]['description']));
//			}
//		}

        $myapplicationids = DB::insert('t_custom_myapplication', $myapplications);

        return array('myapplicationids' => $myapplicationids);
    }


    public function get($options = array())
    {
        $result = array();

        $sqlParts = array(
            'select' => array('t_custom_myapplication' => 'myapp.applicationid'),
            'from' => array('t_custom_myapplication' => 't_custom_myapplication myapp'),
            'where' => array(),
            'group' => array(),
            'order' => array(),
            'limit' => null
        );

        $defOptions = array(
            'applicationids' => null,
            'editable' => null,
            // filter
            'filter' => null,
            'search' => null,
            'searchByAny' => null,
            'startSearch' => null,
            'excludeSearch' => null,
            'searchWildcardsEnabled' => null,
            // output
            'output' => API_OUTPUT_REFER,
            'selectUsers' => null,
            'countOutput' => null,
            'groupCount' => null,
            'preservekeys' => null,
            'sortfield' => '',
            'sortorder' => '',
            'limit' => null
        );
        $options = zbx_array_merge($defOptions, $options);

        // mediatypeids
        if (!is_null($options['applicationids'])) {
            zbx_value2array($options['applicationids']);
            $sqlParts['where'][] = dbConditionInt('myapp.applicationid', $options['applicationids']);
        }

        // filter
        if (is_array($options['filter'])) {
            $this->dbFilter('t_custom_myapplication myapp', $options, $sqlParts);
        }

        // search
        if (is_array($options['search'])) {
            zbx_db_search('t_custom_myapplication myapp', $options, $sqlParts);
        }

        // limit
        if (zbx_ctype_digit($options['limit']) && $options['limit']) {
            $sqlParts['limit'] = $options['limit'];
        }

        $sqlParts = $this->applyQueryOutputOptions($this->tableName(), $this->tableAlias(), $options, $sqlParts);
        $sqlParts = $this->applyQuerySortOptions($this->tableName(), $this->tableAlias(), $options, $sqlParts);
        $sqlParts = $this->applyQueryNodeOptions($this->tableName(), $this->tableAlias(), $options, $sqlParts);
        $res = DBselect($this->createSelectQueryFromParts($sqlParts), $sqlParts['limit']);
        while ($myapplication = DBfetch($res)) {
            if (!is_null($options['countOutput'])) {
                if (!is_null($options['groupCount'])) {
                    $result[] = $myapplication;
                } else {
                    $result = $myapplication['rowscount'];
                }
            } else {
                if (!isset($result[$myapplication['applicationid']])) {
                    $result[$myapplication['applicationid']] = array();
                }
                $result[$myapplication['applicationid']] += $myapplication;
            }
        }

        if (!is_null($options['countOutput'])) {
            return $result;
        }

        // removing keys (hash -> array)
        if (is_null($options['preservekeys'])) {
            $result = zbx_cleanHashes($result);
        }
        return $result;
    }


    public function update($myApplications)
    {
        $myApplications = zbx_toArray($myApplications);

        $update = array();
        foreach ($myApplications as $myApplication) {
            $myApplicationid = $myApplication['applicationid'];
            unset($myApplication['applicationid']);

            if (!empty($myApplication)) {
                $update[] = array(
                    'values' => $myApplication,
                    'where' => array('applicationid' => $myApplicationid)
                );
            }
        }

        DB::update('t_custom_myapplication', $update);
        $applicationids = zbx_objectValues($myApplications, 'applicationid');

        return array('applicationids' => $applicationids);
    }

    public function delete($applicationids)
    {
        $applicationids = zbx_toArray($applicationids);
        DB::delete('t_custom_myapplication', array('applicationid' => $applicationids));
        return array('applicationids' => $applicationids);
    }


    public function deploy($applicationids)
    {
        $applicationids = zbx_toArray($applicationids);

        $myapplications = self::get(array(
            'applicationids' => $applicationids,
             'output' => API_OUTPUT_EXTEND
        ));

        foreach($myapplications as $myapplication)
        {
            $hostids = DBfetchArray(DBselect('select h.hostid from t_custom_hostapps h,t_custom_myapplication a where h.applicationid = a.applicationid and a.applicationid = '.$myapplication['applicationid']));

            $hosts = API::Host()->get(array(
                'hostids' => zbx_objectValues($hostids, 'hostid'),
                'selectInterfaces' => API_OUTPUT_EXTEND,
            ));

            foreach ($hosts as $host) {
                $agent_ip = null;
                $agent_port = null;
                foreach($host['interfaces'] as $interface)
                {
                    if($interface['type'] == '5' && $interface['main'] == '1' )
                    {
                        $agent_ip = $interface['ip'];
                        $agent_port = $interface['port'];
                        break;
                    }
                }

                if(empty($agent_port) || empty($agent_ip))
                {
                    return array('result'=>false,'message'=>'管理代理接口或者ip为空，部署失败！');
                }

                global  $FTP;
                $agent_app_msg="{servertype:'tomcat',optype:'12',args:{app_ftp_ip:'".$FTP['FTP_HOST']."',app_ftp_port:'".$FTP['FTP_PORT']."',app_ftp_name:'".$FTP['FTP_USER']."',app_ftp_password:'".$FTP['FTP_PASS']."',app_file_name:'".$myapplication['filename']."'}}";
                $response_result = AgentManager::send($agent_ip,$agent_port,$agent_app_msg);
                if(null == $response_result || $response_result['result'] == 'false')
                {
                    return array('result'=>false,'message'=>'部署失败！');
                }
                else
                {
                    DBstart();
                    $xx = DB::update('t_custom_myapplication', array(
                        'values' => array('status'=>'3'),
                        'where' => array('applicationid' => $myapplication['applicationid'])
                    ));
                    DBend($xx);
                }

            }
        }
        return array('result'=>true,'message'=>'部署成功！');
    }



}
