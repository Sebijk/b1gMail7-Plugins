<?php
/**
* b1gMail Basic Auth
*
* @author Home of the Sebijk.com <http://www.sebijk.com>
* @license LGPL
* @package b1gMail
*/
define('INTERFACE_MODE', true);
define('DEBUG_MODE', 0);

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="b1gMail"');
    header('HTTP/1.0 401 Unauthorized');
} else {
        require('../serverlib/init.inc.php');

        if(DEBUG_MODE == 1) {
                PutLog(sprintf('[BasicAuth] BasicAuth started.'),PRIO_NOTE,__FILE__,__LINE__);
        }

            // Forbid Alias Login
            $res = $db->Query('SELECT {pre}users.id AS id FROM {pre}users,{pre}aliase WHERE {pre}aliase.email=? AND ({pre}aliase.type&'.ALIAS_RECIPIENT.')!=0 AND {pre}users.id={pre}aliase.user ' . ($excludeDeleted ? 'AND {pre}users.gesperrt!=\'delete\' ' : '') . 'LIMIT 1',
        $_SERVER['PHP_AUTH_USER']);
            if($res->RowCount() == 1) {
              PutLog(sprintf('[basicAuth] login as <%s> failed (alias login is forbidden).',$_SERVER['PHP_AUTH_USER']),PRIO_NOTE,__FILE__,__LINE__);
              header('HTTP/1.0 401 Unauthorized');
              exit;
            }
            // User Login
            list($result, $userID) = BMUser::Login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'], false, false);

            if($result === 0)
            {
                PutLog(sprintf('[basicAuth] login as <%s> successful.',$_SERVER['PHP_AUTH_USER']),PRIO_NOTE,__FILE__,__LINE__);
            }
            else {
                header('HTTP/1.0 401 Unauthorized');
            }
}
