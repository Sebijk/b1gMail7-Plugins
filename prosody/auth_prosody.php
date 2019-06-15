#!/usr/bin/php
<?php
/**
 * Prosody XMPP Server External Authentication w/ b1gMail
 *
 *
 * <http://prosody.im/>
 * <https://code.google.com/p/prosody-modules/wiki/mod_auth_external>
 *
 *
 * @author Home of the Sebijk.com <http://www.sebijk.com>
 * @original author Ville Korhonen <ville.korhonen@ylioppilastutkinto.fi>
 * @original script <https://github.com/digabi/digabi-misc/blob/master/prosody/prosody_auth_external.php>
 * @license GPLv3
 * @version 0.9.3
 * @package b1gMail
 */
/*
 Config:
  /etc/prosody/prosody.cfg.lua:
  	allow_unencrypted_plain_auth = true
    authentication = "external"
    external_auth_protocol = "generic"
    external_auth_command = "path/to/this/file"
 Commands: (TBD)
  auth:
   $0 auth:username:domain:password
   $0 auth:ville:digabi.fi:mysecretpassword
  isuser:
   $0 isuser:username:domain
   $0 isuser:ville:digabi.fi
  setpass:
   $0 setpass:username:domain:password
   $0 setpass:ville:digabi.fi:mynewsecretpassword
*/

// Hier Pfad zum b1gMail-Installation anpassen
define("B1GMAIL_PATH", "/pfad/zu/b1gmail");

define("SEPARATOR_CHAR", ":");
define("AUTHLOG", "/var/log/prosody/prosody_external.log");
define('INTERFACE_MODE', true);
define('DEBUG_MODE', 0);

if(DEBUG_MODE == 1) {

	// If Debug Mode is on, you MUST create a logfile with the user, who prosodys run.
	$LogFile = fopen(AUTHLOG, "a") or die("Error opening log file: ".AUTHLOG);

	function writeLog($sMessage)
	{
		global $LogFile;
		if (is_resource($LogFile)) {
			fwrite($LogFile, date("r") ." ". $sMessage ."\n");
		}
	}
}

include(B1GMAIL_PATH.'/serverlib/init.inc.php');

if(DEBUG_MODE == 1) {
	writeLog("[exAuth] start");
	PutLog(sprintf('[Prosody] exAuth started.'),PRIO_NOTE,__FILE__,__LINE__);
	writeLog("[exAuth] include ".B1GMAIL_PATH.'/serverlib/init.inc.php');
}

/**
 * Check if user exists in domain
 * @param string $user Username
 * @param string $domain Domain
 * @return boolean 0 if failure (user doesn't exist in domain), 1 if success (user exists in domain)
 */
function isuser($user, $domain) {
    global $auth;
    if(DEBUG_MODE == 1) writeLog("[exAuth] check".$user."@".$domain);
    if(empty($user) || empty($domain))
    	return 0;
    else
    	return 1;
}
/**
 * Change user password
 *
 * @todo Should this be able to create new users?
 * @todo Should this do some sort of testing? (User exists in domain etc.?)
 *
 * @param string $user Username
 * @param string $domain Domain
 * @param string $password New password
 * @return boolean 0 if failure (password wasn't changed), 1 if success (password was changed)
 */
function setpass($user, $domain, $password) {
    // TODO
    return 0;
}
/**
 * Authenticate user (check, that user exists in domain w/ specified password)
 *
 * @param string $user Username
 * @param string $domain Domain
 * @param string $password Password
 * @return boolean 0 if failure (user doesn't exist in domain, or password is invalid), 1 if success (user exists in domain, password is valid)
 */
function auth($user, $domain, $password) {
    $loginuser = $user."@".$domain;
    if(DEBUG_MODE == 1) writeLog("[exAuth] Try to login as ".$loginuser);
    list($result, $userID) = BMUser::Login($loginuser, $password, false, false);

    if($result === 0)
    {
    	PutLog(sprintf('[Prosody] login as <%s> successful.',$loginuser),PRIO_NOTE,__FILE__,__LINE__);
    	return 1;
    }
    else {
    	PutLog(sprintf('[Prosody] login as <%s> failed.',$loginuser),PRIO_NOTE,__FILE__,__LINE__);
    	return 0;
    }    
}
/**
 * CLI
 */
function cli() {
    // Parse STDIN, remove trailing whitespace, split at first SEPARATOR_CHAR, so we get command & "the rest of input"
    $command = explode(SEPARATOR_CHAR, trim(fgets(STDIN)), 2);
    
    // Split username, domain and password (in this order, max. 3 separate pieces, password might contain SEPARATOR_CHARs)
    $params = explode(SEPARATOR_CHAR, $command[1], 3);
    
    switch ($command[0]) {
        case "auth":
            return auth($params[0], $params[1], $params[2]);
            break;
        case "isuser":
            return isuser($params[0], $params[1]);
            break;
        case "setpass":
            return setpass($params[0], $params[1], $params[2]);
            break;
        default:
            return 0;
    }
    return $res;
}
if (php_sapi_name() == 'cli') {echo cli();}
?>