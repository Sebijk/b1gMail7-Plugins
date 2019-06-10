<?php
/**
 * Copyright (c) 2012 Robin Appelman <icewind@owncloud.com>, Home of the Sebijk.com
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */

/**
 * User authentication against an b1gMail database server
 *
 * @category Apps
 * @package  UserExternal
 * @author   Home of the Sebijk.com, Robin Appelman <icewind@owncloud.com>
 * @license  http://www.gnu.org/licenses/agpl AGPL
 * @link     http://github.com/owncloud/apps
 */
class OC_User_b1gMail extends OC_User_Backend {


	/**
     * Create new b1gMail authentication provider
     *
     * Dummy
     */
    public function __construct($mailbox) {
        $this->mailbox=$mailbox;
    } 

	/**
	 * Check if the password is correct without logging in the user
	 *
	 * @param string $uid      The username
	 * @param string $password The password
	 *
	 * @return true/false
	 */
	public function checkPassword($uid, $password) {
		$DBServer = "localhost";
        $DBUser = "DEINDBBENUTZER";
        $DBPass = "DEINDBPASSWORT";
        $DBName = "DEINEDB";
        $DBPrefix ="bm60_";
		$b1gmailconn = new mysqli($DBServer, $DBUser, $DBPass, $DBName);
		if (mysqli_connect_errno()) {
			return false;
		}
		$query = $b1gmailconn->query("SELECT email,passwort_salt,passwort,gruppe,absendername,vorname,nachname FROM ".$DBPrefix."users WHERE locked = 'no' AND email = '".$b1gmailconn->escape_string($uid)."'");
		if($query === FALSE) {
			error_log("Owncloud: b1gMail usertable not found");
			return false;
		}
		else {
			$mysql_attr = $query->fetch_array(MYSQLI_ASSOC);
			$md5_passwort = md5(md5($password).$mysql_attr['passwort_salt']);
		}
		unset($query);
		$query = $b1gmailconn->query("SELECT email,passwort,gruppe FROM ".$DBPrefix."users WHERE passwort = '".$md5_passwort."' AND email = '".$b1gmailconn->escape_string($uid)."'");
		$result = $query->fetch_array(MYSQLI_ASSOC);
		if($result)
		{
			$uid = mb_strtolower($uid);
			if(OC_User::userExists($uid)) 
			{
            	OC_User::setPassword($uid, $password);
            } 
            else 
            {
				OC_User::createUser($uid, $password);
				if(!empty($mysql_attr['absendername'])) {
					OC_User::setDisplayName($uid, $mysql_attr['absendername']);
				} else if (!empty($mysql_attr['nachname']) AND !empty($mysql_attr['vorname'])) {
					OC_User::setDisplayName($uid, $mysql_attr['vorname']." ".$mysql_attr['nachname']);
				}
				\OC::$server->getConfig()->setUserValue( $uid, 'settings', 'email', $uid);
				$query = $b1gmailconn->query("SELECT titel FROM ".$DBPrefix."gruppen WHERE id = '".$mysql_attr['gruppe']."'");
				if($query === FALSE) {
					return false;
				}
				else {
					$mysql_attr = $query->fetch_array(MYSQLI_ASSOC);
					$groupname = $mysql_attr['titel'];
					if($groupname == "Administratoren") $groupname = "admin";
					if($groupname == "Administrator") $groupname = "admin";
					OC_Group::createGroup($groupname);
					OC_Group::addToGroup($uid, $groupname);
					// Quota per Group - paste the below line if you have more
					if($groupname == 'YourGroup')  \OC::$server->getConfig()->setUserValue($uid, 'files', 'quota', '3 GB');
   				}
   			}
		    return $uid;
		}
		else {
			return false;
		}
	}
}
