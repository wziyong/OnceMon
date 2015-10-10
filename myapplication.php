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

$mediaTypeId = get_request('mediatypeid');

/*
 * Permissions
 */
if (isset($_REQUEST['mediatypeid'])) {
    $mediaTypes = API::Mediatype()->get(array(
        'applicationids' => $mediaTypeId,
        'output' => API_OUTPUT_EXTEND
    ));
    if (empty($mediaTypes)) {
        access_deny();
    }
}
if (isset($_REQUEST['go'])) {
    if (!isset($_REQUEST['mediatypeids']) || !is_array($_REQUEST['mediatypeids'])) {
        access_deny();
    } else {
        $mediaTypeChk = API::Mediatype()->get(array(
            'mediatypeids' => $_REQUEST['mediatypeids'],
            'countOutput' => true
        ));
        if ($mediaTypeChk != count($_REQUEST['mediatypeids'])) {
            access_deny();
        }
    }
}

$_REQUEST['go'] = get_request('go', 'none');

/*
 * Actions
 */
if (isset($_REQUEST['save'])) { //TODO 新增或者修改； 修改的时候，需要将同步到相关联的服务器上；
     $_FILES["import_file"];
     move_uploaded_file($_FILES["import_file"]["tmp_name"],"d:/xx/" . $_FILES["import_file"]["name"]);

     $mediaType = array(
        'name' => get_request('name'),
        'comment' => get_request('comment'),
        'filename' => $_FILES["import_file"]["name"]
    );

    if ($mediaTypeId) {
        $mediaType['mediatypeid'] = $mediaTypeId;
        $result = API::Mediatype()->update($mediaType);

        $action = AUDIT_ACTION_UPDATE;
        show_messages($result, _('Media type updated'), _('Cannot update media type'));
    } else {
        $result = API::MyApplication()->create($mediaType);

        $action = AUDIT_ACTION_ADD;
        show_messages($result, _('应用已新增'), _('新增应用失败'));
    }

    if ($result) {
        add_audit($action, AUDIT_RESOURCE_MEDIA_TYPE, 'Media type [' . $mediaType['description'] . ']');
        unset($_REQUEST['form']);
        clearCookies($result);
    }
} elseif (isset($_REQUEST['delete']) && !empty($mediaTypeId)) { // TODO 删除需要考虑关联的应用服务器；
    $result = API::Mediatype()->delete($_REQUEST['mediatypeid']);

    if ($result) {
        unset($_REQUEST['form']);
    }

    show_messages($result, _('Media type deleted'), _('Cannot delete media type'));
    clearCookies($result);
} elseif ($_REQUEST['go'] == 'delete') {
    $goResult = API::Mediatype()->delete(get_request('mediatypeids', array()));

    show_messages($goResult, _('Media type deleted'), _('Cannot delete media type'));
    clearCookies($goResult);
}

/*
 * Display
 */
if (!empty($_REQUEST['form'])) {
    $data = array(
        'form' => get_request('form'),
        'form_refresh' => get_request('form_refresh', 0),
        'mediatypeid' => $mediaTypeId
    );

    if (isset($data['mediatypeid']) && empty($_REQUEST['form_refresh'])) {
        $mediaType = reset($mediaTypes);

        $data['type'] = $mediaType['type'];
        $data['description'] = $mediaType['description'];
        $data['smtp_server'] = $mediaType['smtp_server'];
        $data['smtp_helo'] = $mediaType['smtp_helo'];
        $data['smtp_email'] = $mediaType['smtp_email'];
        $data['exec_path'] = $mediaType['exec_path'];
        $data['gsm_modem'] = $mediaType['gsm_modem'];
        $data['username'] = $mediaType['username'];
        $data['password'] = $mediaType['passwd'];
        $data['status'] = $mediaType['status'];
    } else {
        $data['type'] = get_request('type', MEDIA_TYPE_EMAIL);
        $data['description'] = get_request('description', '');
        $data['smtp_server'] = get_request('smtp_server', 'localhost');
        $data['smtp_helo'] = get_request('smtp_helo', 'localhost');
        $data['smtp_email'] = get_request('smtp_email', 'zabbix@localhost');
        $data['exec_path'] = get_request('exec_path', '');
        $data['gsm_modem'] = get_request('gsm_modem', '/dev/ttyS0');
        $data['username'] = get_request('username', ($data['type'] == MEDIA_TYPE_EZ_TEXTING) ? 'username' : 'user@server');
        $data['password'] = get_request('password', '');
        $data['status'] = get_request('status', MEDIA_TYPE_STATUS_ACTIVE);
    }

    // render view
    $mediaTypeView = new CView('configuration.myapplication.edit', $data);
    $mediaTypeView->render();
    $mediaTypeView->show();
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
    $mediaTypeView = new CView('configuration.myapplication.list', $data);
    $mediaTypeView->render();
    $mediaTypeView->show();
}

require_once dirname(__FILE__) . '/include/page_footer.php';
