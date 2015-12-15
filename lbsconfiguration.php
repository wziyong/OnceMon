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


$page['title'] = _('负载均衡器配置');
$page['file'] = 'lbsconfiguration.php';
$page['hist_arg'] = array();

require_once dirname(__FILE__) . '/include/page_header.php';

// VAR	TYPE	OPTIONAL	FLAGS	VALIDATION	EXCEPTION
$fields = array(
    // actions
    'save' => array(T_ZBX_STR, O_OPT, P_SYS | P_ACT, null, null),
    'go' => array(T_ZBX_STR, O_OPT, P_SYS | P_ACT, null, null),
    'form' => array(T_ZBX_STR, O_OPT, P_SYS, null, null),
);
//check_fields($fields);
validate_sort_and_sortorder('description', ZBX_SORT_UP);

$applicationId = get_request('applicationid');

/*
 * Permissions
 */

$_REQUEST['go'] = get_request('go', 'none');

/*
 * Actions
 */
if (isset($_REQUEST['save'])) {
    $dbHosts = API::Host()->get(array(
        'hostids' => $_REQUEST['hostid'],
        'selectInterfaces' => API_OUTPUT_EXTEND,
    ));
    if(empty($dbHosts))
    {
        show_messages(false, '', _('服务器不存在'));
    }
    else
    {
        $host = $dbHosts[0];

        $filename = $_FILES["import_file"]["name"];
        if("" == $filename || null == $filename )
        {
            show_messages(false, '', _('未选择配置文件'));
        }
        else{
            $ftp = new class_ftp($FTP['FTP_HOST'],$FTP['FTP_PORT'],$FTP['FTP_USER'],$FTP['FTP_PASS']); // 打开FTP连接
            $isUpload = $ftp->up_file($_FILES["import_file"]["tmp_name"],"~/".$filename);

            if($isUpload)//上传ftp成功后，更新到数据库
            {
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

                //$agent_app_msg="{servertype:'nginx',optype:'11',args:{}}";
                $agent_app_msg="{servertype:'nginx',optype:'11',args:{app_ftp_ip:'".$FTP['FTP_HOST']."',app_ftp_port:'".$FTP['FTP_PORT']."',app_ftp_name:'".$FTP['FTP_USER']."',app_ftp_password:'".$FTP['FTP_PASS']."',nginx_cfg_file_name:'nginx.conf'}}";
                $response_result = AgentManager::send($agent_ip,$agent_port,$agent_app_msg);
                if($response_result['result']) {
                    show_messages(true, '', _('成功'));
                }
                else
                {
                    show_messages(false, '', _('负载均衡器配置文件失败'));
                }

            }
            else{
                show_messages(false, '', _('配置文件上传FTP失败'));
            }
        }
    }
}

/*
 * Display
 */

if (empty($_REQUEST['form'])) {
    $data = array(
        'form' => get_request('form'),
        'form_refresh' => get_request('form_refresh', 0),
        'applicationid' => '',
    );

    $data['filename'] = get_request('filename');

    $widget = new CWidget();
    $widget->addPageHeader(_('负载均衡器配置'));

    $form = new CForm('post', null, 'multipart/form-data');
    $form->setName('myApplicationForm');
    $form->addVar('form',null);
    $form->addVar('form_refresh', null);
    $form->addVar('applicationid', null);

    // create form list
    $lbsCfgFormList = new CFormList('myApplicationFormList');
    $file =  new CFile('import_file');
    $file->attr('style','width:318px');
    $lbsCfgFormList->addRow(_('配置文件'),$file);

    $hostid= new CInput('hidden','hostid',get_request('hostid'));
    $lbsCfgFormList->addItem($hostid);

    // append form list to tab
    $tab = new CTabView();
    $tab->addTab('mediaTypeTab', _('配置'), $lbsCfgFormList);

    $form->addItem($tab);
    $form->addItem(makeFormFooter(new CSubmit('save', _('上传')), array(new CButtonCancel())));

    $widget->addItem($form);
    $widget->show();
}

require_once dirname(__FILE__) . '/include/page_footer.php';
