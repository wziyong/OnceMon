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
$page['file'] = 'myapplication.php';
$page['hist_arg'] = array();

require_once dirname(__FILE__) . '/include/page_header.php';

// VAR	TYPE	OPTIONAL	FLAGS	VALIDATION	EXCEPTION
$fields = array(
    'mediatypeids' => array(T_ZBX_INT, O_OPT, P_SYS, DB_ID, null),
    'mediatypeid' => array(T_ZBX_INT, O_NO, P_SYS, DB_ID, 'isset({form})&&{form}=="edit"'),
    'type' => array(T_ZBX_INT, O_OPT, null, IN(implode(',', array_keys(media_type2str()))), 'isset({save})'),
    'description' => array(T_ZBX_STR, O_OPT, null, NOT_EMPTY, 'isset({save})'),
    'smtp_server' => array(T_ZBX_STR, O_OPT, null, NOT_EMPTY,
        'isset({save})&&isset({type})&&{type}==' . MEDIA_TYPE_EMAIL),
    'smtp_helo' => array(T_ZBX_STR, O_OPT, null, NOT_EMPTY,
        'isset({save})&&isset({type})&&{type}==' . MEDIA_TYPE_EMAIL),
    'smtp_email' => array(T_ZBX_STR, O_OPT, null, NOT_EMPTY,
        'isset({save})&&isset({type})&&{type}==' . MEDIA_TYPE_EMAIL),
    'exec_path' => array(T_ZBX_STR, O_OPT, null, NOT_EMPTY,
        'isset({save})&&isset({type})&&({type}==' . MEDIA_TYPE_EXEC . '||{type}==' . MEDIA_TYPE_EZ_TEXTING . ')'),
    'gsm_modem' => array(T_ZBX_STR, O_OPT, null, NOT_EMPTY,
        'isset({save})&&isset({type})&&{type}==' . MEDIA_TYPE_SMS),
    'username' => array(T_ZBX_STR, O_OPT, null, NOT_EMPTY,
        'isset({save})&&isset({type})&&({type}==' . MEDIA_TYPE_JABBER . '||{type}==' . MEDIA_TYPE_EZ_TEXTING . ')'),
    'password' => array(T_ZBX_STR, O_OPT, null, NOT_EMPTY,
        'isset({save})&&isset({type})&&({type}==' . MEDIA_TYPE_JABBER . '||{type}==' . MEDIA_TYPE_EZ_TEXTING . ')'),
    'status' => array(T_ZBX_INT, O_OPT, null, IN(array(MEDIA_TYPE_STATUS_ACTIVE, MEDIA_TYPE_STATUS_DISABLED)), null),
    // actions
    'save' => array(T_ZBX_STR, O_OPT, P_SYS | P_ACT, null, null),
    'delete' => array(T_ZBX_STR, O_OPT, P_SYS | P_ACT, null, null),
    'cancel' => array(T_ZBX_STR, O_OPT, P_SYS | P_ACT, null, null),
    'go' => array(T_ZBX_STR, O_OPT, P_SYS | P_ACT, null, null),
    'form' => array(T_ZBX_STR, O_OPT, P_SYS, null, null),
    'form_refresh' => array(T_ZBX_INT, O_OPT, null, null, null)
);
//check_fields($fields);
validate_sort_and_sortorder('description', ZBX_SORT_UP);

$applicationId = get_request('applicationid');

/*
 * Permissions
 */
if (isset($_REQUEST['applicationid'])) {
    $myApplications = API::MyApplication()->get(array(
        'applicationids' => $applicationId,
        'output' => API_OUTPUT_EXTEND
    ));
    if (empty($myApplications)) {
        access_deny();
    }
}
if (isset($_REQUEST['go'])) {
    if (!isset($_REQUEST['applicationids']) || !is_array($_REQUEST['applicationids'])) {
        access_deny();
    } else {
        $mediaTypeChk = API::MyApplication()->get(array(
            'applicationids' => $_REQUEST['applicationids'],
            'countOutput' => true
        ));
        if ($mediaTypeChk != count($_REQUEST['applicationids'])) {
            access_deny();
        }
    }
}

$_REQUEST['go'] = get_request('go', 'none');

/*
 * Actions
 */
if (isset($_REQUEST['save'])) { //TODO 新增或者修改； 修改的时候，需要将同步到相关联的服务器上；
     $filename = $_FILES["import_file"]["name"];
     if("" == $filename || null == $filename )
     {
         show_messages(false, '', _('未选择应用安装包'));
     }
     else{
        $ftp = new class_ftp($FTP['FTP_HOST'],$FTP['FTP_PORT'],$FTP['FTP_USER'],$FTP['FTP_PASS']); // 打开FTP连接
        $isUpload = $ftp->up_file($_FILES["import_file"]["tmp_name"],"~/".$filename);

         if($isUpload)//上传ftp成功后，更新到数据库
         {
             $myApplication = array(
                 'name' => get_request('name'),
                 'comment' => get_request('comment'),
                 'filename' =>$filename
             );

             if ($applicationId) {
                 $myApplication['applicationid'] = $applicationId;
                 $result = API::MyApplication()->update($myApplication);
                 $action = AUDIT_ACTION_UPDATE;
                 show_messages($result, _('更新应用成功'), _('更新应用失败'));
             } else {
                 $result = API::MyApplication()->create($myApplication);
                 $action = AUDIT_ACTION_ADD;
                 show_messages($result, _('应用已新增'), _('新增应用失败'));
             }
             if ($result) {
                 add_audit($action, AUDIT_RESOURCE_MEDIA_TYPE, 'Media type [' . $myApplication['comment'] . ']');
                 unset($_REQUEST['form']);
                 clearCookies($result);
             }
         }
         else{
             show_messages(false, '', _('安装包上传FTP失败'));
         }
     }

} elseif (isset($_REQUEST['delete']) && !empty($applicationId)) { // TODO 删除需要考虑关联的应用服务器；
    $result = API::Mediatype()->delete($_REQUEST['mediatypeid']);

    if ($result) {
        unset($_REQUEST['form']);
    }

    show_messages($result, _('Media type deleted'), _('Cannot delete media type'));
    clearCookies($result);
} elseif ($_REQUEST['go'] == 'delete') {
    $goResult = API::MyApplication()->delete(get_request('applicationids', array()));

    show_messages($goResult, _('删除应用成功'), _('删除应用失败'));
    clearCookies($goResult);
}

/*
 * Display
 */
if (!empty($_REQUEST['form'])) {
    $data = array(
        'form' => get_request('form'),
        'form_refresh' => get_request('form_refresh', 0),
        'applicationid' => $applicationId
    );

    if (isset($data['applicationid']) && empty($_REQUEST['form_refresh'])) {
        $myApplication = reset($myApplications);
        $data['name'] = $myApplication['name'];
        $data['filename'] = $myApplication['filename'];
        $data['comment'] = $myApplication['comment'];
    } else {
        $data['name'] = get_request('name');
        $data['filename'] = get_request('filename');
        $data['comment'] = get_request('comment');
    }

    // render view
    $myApplicationView = new CView('configuration.myapplication.edit', $data);
    $myApplicationView->render();
    $myApplicationView->show();

} else {
    $data = array(
        'displayNodes' => is_array(get_current_nodeid())
    );

    $data['myapplications'] = API::MyApplication()->get(array(
        'output' => API_OUTPUT_EXTEND,
        'preservekeys' => true,
        'editable' => true,
        'limit' => $config['search_limit'] + 1
    ));

    if ($data['myapplications']) {
        // get media types used in actions
        $actions = API::Action()->get(array(
            'applicationids' => zbx_objectValues($data['myapplications'], 'applicationid'),
            'output' => array('actionid', 'name'),
            'selectOperations' => array('operationtype', 'opmessage'),
            'preservekeys' => true
        ));

        // sorting & paging
        order_result($data['myapplications'], getPageSortField('name'), getPageSortOrder());
        $data['paging'] = getPagingLine($data['myapplications'], array('applicationid'));

    } else {
        $arr = array();
        $data['paging'] = getPagingLine($arr, array('applicationid'));
    }

    // render view
    $myApplicationView = new CView('configuration.myapplication.list', $data);
    $myApplicationView->render();
    $myApplicationView->show();
}

require_once dirname(__FILE__) . '/include/page_footer.php';
