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


require_once dirname(__FILE__) . '/include/config.inc.php';
require_once dirname(__FILE__) . '/include/media.inc.php';
require_once dirname(__FILE__) . '/include/forms.inc.php';


$page['title'] = _('统一部署');
$page['file'] = 'myapplicationdeploygroup.php';
$page['hist_arg'] = array();
$page['scripts'] = array('multiselect.js');

require_once dirname(__FILE__) . '/include/page_header.php';

//require_once dirname(__FILE__) . '/include/views/js/custom/custom.myapplication.deploy.js.php';

/*
 * Actions
 */
if (isset($_REQUEST['add_application'])) {
    $applications = $_REQUEST['add_applications'];
    $groupid = $_REQUEST['groupid'];

    if(!empty($groupid) && !empty($applications))
    {
        $groups = API::HostGroup()->get(array(
            'groupids' => $groupid,
            'selectHosts' => array('hostid', 'name', 'server_type'),
            'output' => API_OUTPUT_EXTEND,
        ));

        if(!empty($groups))
        {
            $group = $groups[0];
            if(!empty($group['hosts']))
            {
                $myapplicationTmp = DBselect('select * from t_custom_myapplication t1 where t1.applicationid in ('.implode(',',$applications).')');
                $myapplications = array();
                while ($myapplication = DBfetch($myapplicationTmp)) {
                    $myapplications['applicationid'] =$myapplication['filename'];
                }
                $hosts = $group['hosts'];

                $error = 0;
                DBstart();
                foreach($hosts as $host)
                {
                    if($host['server_type'] != '1')
                    {
                        continue;
                    }

                    $dbHost = API::Host()->get(array(
                        'hostids' => $host['hostid'],
                        'selectInterfaces' => API_OUTPUT_EXTEND,
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
                        $error++;
                        show_messages(false, null, "部署失败！主机没有管理接口，HOST ID:".$host['hostid']);
                        break;
                    }

                    global $FTP;
                    $agent_app_msg="{servertype:'tomcat',optype:'12',args:{app_ftp_ip:'".$FTP['FTP_HOST']."',app_ftp_port:'".$FTP['FTP_PORT']."',app_ftp_name:'".$FTP['FTP_USER']."',app_ftp_password:'".$FTP['FTP_PASS']."',app_file_name:'".implode(',',$myapplications)."'}}";
                    $response_result = AgentManager::send($agent_ip,$agent_port,$agent_app_msg);
                    if(null == $response_result || $response_result['result'] == 'false')
                    {
                        $error++;
                        show_messages(false, null, "部署失败！管理接口响应失败:".$response_result['message']."HOST ID:".$host['hostid']);
                        break;
                    }

                    $errorCount = 0;
                    foreach($applications as $tempX)
                    {
                        $sql =  'INSERT INTO t_custom_hostapps (hostid, applicationid) SELECT '.$host['hostid'].', '.$tempX.' FROM dual WHERE not exists (select * from t_custom_hostapps where hostid = '.$host['hostid'].' and applicationid = '.$tempX.')';
                        $xx =  DBexecute($sql);
                        if(!$xx)
                        {
                            $errorCount++;
                            break;
                        }
                    }

                    if($errorCount>0)
                    {
                        $error++;
                        DBend(false);
                        show_messages(false, null, "部署失败！更新数据库失败 HOST ID:".$host['hostid']);
                        break;
                    }
                }

                if( $error == 0 )
                {
                    $xxerror = 0;
                    foreach($applications as $tempX)
                    {
                        $xx =  DBexecute('INSERT INTO t_custom_groupapps (groupid, applicationid) VALUES ('.$group['groupid'].', '.$tempX.')');
                        if(!$xx)
                        {
                            $xxerror++;
                            break;
                        }
                    }
                    if($xxerror == 0)
                    {
                        DBend(true);
                        show_messages(true, null, "统一部署成功");
                    }
                    else{
                        DBend(false);
                        show_messages(false, null, "统一部署失败");
                    }
                }
                else
                {
                    show_messages(false, null, "统一部署失败");
                }
            }
            else
            {
                show_messages(false, null, "该集群下无主机，ID:".$groupid);
            }
        }
        else
        {
            show_messages(false, null, "未查询到该集群，ID:".$groupid);
        }
    }
    else
    {
        show_messages(false, null, "集群id为空");
    }


} elseif (isset($_REQUEST['unlink'])) {
    $applications = isset($_REQUEST['selectedMyApplicationids']) ?$_REQUEST['selectedMyApplicationids']:array();
    $groupid = $_REQUEST['groupid'];

    if(!empty($groupid) && !empty($applications))
    {
        $groups = API::HostGroup()->get(array(
            'groupids' => $groupid,
            'selectHosts' => array('hostid', 'name', 'server_type'),
            'output' => API_OUTPUT_EXTEND,
        ));

        if(!empty($groups))
        {
            $group = $groups[0];
            if(!empty($group['hosts']))
            {
                $myapplicationTmp = DBselect('select * from t_custom_myapplication t1 where t1.applicationid in ('.implode(',',$applications).')');
                $myapplications = array();
                while ($myapplication = DBfetch($myapplicationTmp)) {
                    $myapplications['applicationid'] =$myapplication['filename'];
                }
                $hosts = $group['hosts'];

                $error = 0;
                DBstart();
                foreach($hosts as $host)
                {
                    if($host['server_type'] != '1')
                    {
                        continue;
                    }

                    $dbHost = API::Host()->get(array(
                        'hostids' => $host['hostid'],
                        'selectInterfaces' => API_OUTPUT_EXTEND,
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
                        $error++;
                        show_messages(false, null, "反部署失败！主机没有管理接口，HOST ID:".$host['hostid']);
                        break;
                    }

                    $agent_app_msg="{servertype:'tomcat',optype:'13',args:{app_file_name:'".implode(',',$myapplications)."'}}";
                    $response_result = AgentManager::send($agent_ip,$agent_port,$agent_app_msg);
                    if(null == $response_result || $response_result['result'] == 'false')
                    {
                        $error++;
                        show_messages(false, null, "反部署失败！管理接口响应失败:".$response_result['message']."HOST ID:".$host['hostid']);
                        break;
                    }

                    $xx =   DBexecute('delete from t_custom_hostapps where hostid= '.$host['hostid'].' and applicationid in ('.implode(',',$applications).')');

                    if(!$xx)
                    {
                        $error++;
                        DBend(false);
                        show_messages(false, null, "反部署失败！更新数据库失败 HOST ID:".$host['hostid']);
                        break;
                    }
                }

                if( $error == 0 )
                {
                    $xxX =   DBexecute('delete from t_custom_groupapps where groupid= '.$group['groupid'].' and applicationid in ('.implode(',',$applications).')');
                    if($xxX)
                    {
                        DBend(true);
                        show_messages(true, null, "统一反部署成功");
                    }
                    else{
                        DBend(false);
                        show_messages(false, null, "统一反部署失败");
                    }
                }
                else
                {
                    show_messages(false, null, "统一反部署失败");
                }
            }
            else
            {
                show_messages(false, null, "该集群下无主机，ID:".$groupid);
            }
        }
        else
        {
            show_messages(false, null, "未查询到该集群，ID:".$groupid);
        }
    }
    else
    {
        show_messages(false, null, "集群id为空");
    }
}

/*
 * Display
 */

if(true)
{
    $groupid = $_REQUEST['groupid'];

    $groups = API::HostGroup()->get(array(
        'groupids' => $groupid,
        'selectHosts' => array('hostid', 'name', 'server_type'),
        'output' => API_OUTPUT_EXTEND,
    ));

    $linkedApplications = DBfetchArray(DBselect('select t2.name,t2.applicationid from  t_custom_groupapps t1 , t_custom_myapplication t2  where t1.applicationid = t2.applicationid and t1.groupid = '.$groupid));

    if(!empty($groups))
    {
        $group  = $groups[0];
    }

    //集群列表页面
    $hostGroupWidget = new CWidget();
    //页头label，提示加右侧的创建按钮；
    $tmplList = new CFormList('tmpllist');
    $linkedApplicationTable = new CTable(null, 'formElementTable');
    $linkedApplicationTable->attr('id', 'linkedApplicationTable');

    $linkedApplicationTable->setHeader(array(_('Name'), _('Action')));
    $ignoredTemplates = array();
    $selectedMyApplicationids = new CDiv();
    $selectedMyApplicationids->attr("id","selectedMyApplicationids");

    $ignored = array();
    foreach ($linkedApplications as $application) {
        //$tmplList->addVar('templates[]', $application['templateid']);
        $linkedApplicationTable->addRow(
            array(
                $application['name'],
                array(
                    new CSubmit('unlink[' . $application['applicationid'] . ']', _('反部署'), null, 'link_menu'),
                    SPACE,
                )
            ),
            null, 'conditions_' .  $application['applicationid']
        );
        $ignored[ $application['applicationid']] =  $application['name'];
        $selectedMyApplicationids->addItem(new CInput('hidden','selectedMyApplicationids[]', $application['applicationid']));
    }

    $tmplList->addItem(new CInput('hidden','groupid',$_REQUEST['groupid']));
    $tmplList->addRow($selectedMyApplicationids,null,true);
    $tmplList->addRow(_('部署应用'), new CDiv($linkedApplicationTable, 'objectgroup inlineblock border_dotted ui-corner-all'));

    $frmHost = new CForm();
    $frmHost->setName('web.hosts.host.php.');

    $newTemplateTable = new CTable(null, 'formElementTable2');
    $newTemplateTable->attr('id', 'newTemplateTable2');
    $newTemplateTable->attr('style', 'min-width: 400px;');



    $newTemplateTable->addRow(array(new CMultiSelect(array(
        'name' => 'add_applications[]',
        'objectName' => 'applications',
        'ignored' => $ignored,
        'popup' => array(
            'parameters' => 'srctbl=myapplications&srcfld1=hostid&srcfld2=host&dstfrm=' . $frmHost->getName() .
                '&dstfld1=add_applications_&templated_hosts=1&multiselect=1',
            'width' => 450,
            'height' => 450
        )
    ))));
    $newTemplateTable->addRow(array(new CSubmit('add_application', _('Add'), null, 'link_menu')));

    $tmplList->addRow(_('部署新应用'), new CDiv($newTemplateTable, 'objectgroup inlineblock border_dotted ui-corner-all'));

    $divTabs = new CTabView();
    $divTabs->addTab('myapplicationsTab', _('统一部署'), $tmplList);

    $frmHost->addItem($divTabs);
    $hostGroupWidget->addItem($frmHost);
    $hostGroupWidget->show();
}

require_once dirname(__FILE__) . '/include/page_footer.php';
