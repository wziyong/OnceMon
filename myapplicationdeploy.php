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


require_once dirname(__FILE__) . '/include/classes/class.ftp.php';

$page['title'] = _('应用管理');
$page['file'] = 'myapplicationdeploy.php';
$page['hist_arg'] = array();
$page['scripts'] = array('multiselect.js');

require_once dirname(__FILE__) . '/include/page_header.php';

//require_once dirname(__FILE__) . '/include/views/js/custom/custom.myapplication.deploy.js.php';

/*
 * Actions
 */
if (isset($_REQUEST['add_application'])) {
    $applications = $_REQUEST['add_applications'];
    $hostid = $_REQUEST['hostid'];

    $dbHosts = API::Host()->get(array(
        'hostids' => $hostid,
        'selectInterfaces' => API_OUTPUT_EXTEND,
        'output' => API_OUTPUT_EXTEND
    ));

    if(empty($dbHosts))
    {
        show_messages(false, null, "主机为空");
    }
    elseif(empty($applications))
    {
        show_messages(false, null, "应用列表为空");
    }
    elseif(!empty($dbHosts))
    {
        global $FTP;
        $dbHost = $dbHosts[0];
        $agent_ip = null;
        $agent_port = null;
        foreach($dbHost['interfaces'] as $interfaceX)
        {
            if($interfaceX['type'] == '5' && $interfaceX['main'] == '1')
            {
                $agent_ip = $interfaceX['ip'];
                $agent_port = $interfaceX['port'];
                break;
            }
        }

        if(null!=$agent_port && null!=$agent_ip)
        {
            $myapplicationTmp = DBselect('select * from t_custom_myapplication t1 where t1.applicationid in ('.implode(',',$applications).')');
            $myapplications = array();
            while ($myapplication = DBfetch($myapplicationTmp)) {
                $myapplications[] =array('applicationid'=>$myapplication['applicationid'],'filename'=>$myapplication['filename']);
            }

            $errorCount = 0;
            foreach($myapplications as $tempX)
            {
                $agent_app_msg="{servertype:'tomcat',optype:'12',args:{app_ftp_ip:'".$FTP['FTP_HOST']."',app_ftp_port:'".$FTP['FTP_PORT']."',app_ftp_name:'".$FTP['FTP_USER']."',app_ftp_password:'".$FTP['FTP_PASS']."',app_file_name:'".$tempX['filename']."'}}";
                $response_result = AgentManager::send($agent_ip,$agent_port,$agent_app_msg);
                if(null != $response_result && $response_result['result'] == 'true')
                {
                   $xx =  DBexecute('INSERT INTO t_custom_hostapps (hostid, applicationid) VALUES ('.$hostid.', '.$tempX['applicationid'].')');
                    if(!$xx)
                    {
                        $errorCount++;
                    }
                }
                else
                {
                    $errorCount ++;
                }

            }

            if($errorCount>0)
            {
                show_messages(false, null, "部署应用失败个数".$errorCount);
            }
            else
            {
                show_messages(true, "部署成功", null);
            }
        }
        else{
            show_messages(false, null, "管理接口不可用");
        }
    }
} elseif (isset($_REQUEST['unlink'])) {
    $applications = $_REQUEST['unlink'];
    $hostid = $_REQUEST['hostid'];
    $dbHosts = API::Host()->get(array(
        'hostids' => $hostid,
        'selectInterfaces' => API_OUTPUT_EXTEND,
        'output' => API_OUTPUT_EXTEND
    ));

    if(empty($dbHosts))
    {
        show_messages(false, null, "主机为空");
    }
    elseif(empty($applications))
    {
        show_messages(false, null, "应用列表为空");
    }
    elseif(!empty($dbHosts))
    {
        global $FTP;
        $dbHost = $dbHosts[0];
        $agent_ip = null;
        $agent_port = null;
        foreach($dbHost['interfaces'] as $interfaceX)
        {
            if($interfaceX['type'] == '5' && $interfaceX['main'] == '1')
            {
                $agent_ip = $interfaceX['ip'];
                $agent_port = $interfaceX['port'];
                break;
            }
        }

        if(null!=$agent_port && null!=$agent_ip)
        {
            $myapplicationTmp = DBselect('select * from t_custom_myapplication t1 where t1.applicationid in ('.implode(',',array_keys($applications)).')');
            $myapplications = array();
            while ($myapplication = DBfetch($myapplicationTmp)) {
                $myapplications[] =array('applicationid'=>$myapplication['applicationid'],'filename'=>$myapplication['filename']);
            }

            if(empty($myapplications))
            {
                show_messages(false, null, "没有该应用");
            }
            else{
                $errorCount = 0;
                foreach($myapplications as $tempX)
                {
                    $agent_app_msg="{servertype:'tomcat',optype:'13',args:{app_file_name:'".$tempX['filename']."'}}";
                    $response_result = AgentManager::send($agent_ip,$agent_port,$agent_app_msg);
                    if(null != $response_result && $response_result['result'] == 'true')
                    {
                        $xx =  DBexecute('delete from  t_custom_hostapps  where hostid = '.$hostid.' and applicationid = '.$tempX['applicationid']);
                        if(!$xx)
                        {
                            $errorCount++;
                        }
                    }
                    else
                    {
                        $errorCount ++;
                    }
                }
                if($errorCount>0)
                {
                    show_messages(false, null, "部署应用失败个数:".$errorCount);
                }
                else
                {
                    show_messages(true, "部署成功", null);
                }
            }
        }
        else{
            show_messages(false, null, "管理接口不可用");
        }
    }
}

/*
 * Display
 */

if(true)
{
    $hostId = $_REQUEST['hostid'];
    $dbHosts = API::Host()->get(array(
        'hostids' => $hostId,
        'output' => API_OUTPUT_EXTEND
    ));

    $dbHost = $dbHosts[0];
    //集群列表页面
    $hostGroupWidget = new CWidget();
    //页头label，提示加右侧的创建按钮；
    $tmplList = new CFormList('tmpllist');
    $linkedApplicationTable = new CTable(null, 'formElementTable');
    $linkedApplicationTable->attr('id', 'linkedApplicationTable');
    $linkedApplications = isset($dbHost) && isset($dbHost['selectedMyApplicationids']) ?$dbHost['selectedMyApplicationids']:array();
    CArrayHelper::sort($linkedApplications, array('name'));

    $linkedApplicationTable->setHeader(array(_('Name'), _('Action')));
    $ignoredTemplates = array();
    $selectedMyApplicationids = new CDiv();
    $selectedMyApplicationids->attr("id","selectedMyApplicationids");

    $ignored = array();
    foreach ($linkedApplications as $application) {
        //$tmplList->addVar('templates[]', $application['templateid']);
        $linkedApplicationTable->addRow(
            array(
                $application[key($application)],
                array(
                    new CSubmit('unlink[' . key($application) . ']', _('反部署'), null, 'link_menu'),
                    SPACE,
                )
            ),
            null, 'conditions_' . key($application)
        );
        $ignored[key($application)] = $application[key($application)];
        $selectedMyApplicationids->addItem(new CInput('hidden','selectedMyApplicationids[]',key($application)));
    }

    $tmplList->addItem(new CInput('hidden','type',$_REQUEST['type']));
    $tmplList->addItem(new CInput('hidden','hostid',$_REQUEST['hostid']));
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
    $divTabs->addTab('myapplicationsTab', _('部署应用'), $tmplList);

    $frmHost->addItem($divTabs);
    $hostGroupWidget->addItem($frmHost);
    $hostGroupWidget->show();
}

require_once dirname(__FILE__) . '/include/page_footer.php';
