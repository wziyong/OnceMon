<?php
require_once dirname(__FILE__).'/include/config.inc.php';
require_once dirname(__FILE__).'/include/forms.inc.php';

$parentHosts = API::Host()->getParentHosts(32);
echo json_encode($parentHosts);



?>