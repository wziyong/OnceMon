<?php
require_once dirname(__FILE__).'/include/config.inc.php';
require_once dirname(__FILE__).'/include/forms.inc.php';

$grouid = $_REQUEST['groupid'];
$parentHosts = API::Host()->getParentHosts($grouid);
echo json_encode($parentHosts);



?>