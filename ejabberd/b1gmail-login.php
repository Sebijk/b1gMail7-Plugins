#!/usr/bin/php
<?php

/*
 * ejabberd extauth script, integration with b1gMail
 *
 * based on Dalibor Karlovic <dado@krizevci.info> Joomla Authentication script
 * modifed and improved for b1gMail 7.3.0 by Home of the Sebijk.com
 * published under GPL
 *
 *	- Change it's owner to whichever user is running the server, ie. ejabberd
 *	  $ chown ejabberd:ejabberd /var/lib/ejabberd/b1gmail-login.php
 *
 * 	- Change the access mode so it is readable only to the user ejabberd and has exec
 *	  $ chmod 700 /var/lib/ejabberd/joomla-login
 *
 *	- Edit your ejabberd.cfg file, comment out your auth_method and add:
 *	  {auth_method, external}.
 *	  {extauth_program, "/var/lib/ejabberd/b1gmail-login.php"}.
 *
 *	- Restart your ejabberd service, you should be able to login with your Joomla auth info
 *
 * Other hints:
 *	- if your users have a space or a @ in their username, they'll run into trouble
 *	  registering with any client so they should be instructed to replace these chars
 *	  " " (space) is replaced with "%20"
 *	  "@" is replaced with "(a)"
 *
 *
 */
 
// Hier den Pfad zu b1gMail-Konfiguration einbinden
require_once("/pfad/zum/b1gmail/serverlib/config.inc.php");


$sDBUser 	= $mysql['user'];
$sDBPassword 	= $mysql['pass'];
$sDBHost 	= $mysql['host'];
$sDBName	= $mysql['db'];
$sb1gMailPrefix	= $mysql['prefix'];
unset($mysql);

// the logfile to which to write, should be writeable by the user which is running the server
$sLogFile 	= "/var/log/ejabberd/exauth.log";

// set true to debug if needed
$bDebug		= false;

$oAuth = new exAuth($sDBUser, $sDBPassword, $sDBHost, $sDBName, $sb1gMailPrefix, $sLogFile, $bDebug);

class exAuth
{
	private $sDBUser;
	private $sDBPassword;
	private $sDBHost;
	private $sDBName;
	private $sb1gMailPrefix;
	private $sLogFile;

	private $bDebug;

	private $oMySQL;
	private $rLogFile;
	
	public function __construct($sDBUser, $sDBPassword, $sDBHost, $sDBName, $sb1gMailPrefix, $sLogFile, $bDebug)
	{
		// setter
		$this->sDBUser 		= $sDBUser;
		$this->sDBPassword 	= $sDBPassword;
		$this->sDBHost 		= $sDBHost;
		$this->sDBName 		= $sDBName;
		$this->sb1gMailPrefix 	= $sb1gMailPrefix;
		$this->sLogFile 	= $sLogFile;
		$this->bDebug		= $bDebug;
		
		// ovo ne provjeravamo jer ako ne mozes kreirati log file, onda si u kvascu :)
		$this->rLogFile = fopen($this->sLogFile, "a") or die("Error opening log file: ". $this->sLogFile);

		$this->writeLog("[exAuth] start");
		$this->dbconnect();

		// ovdje bi trebali biti spojeni na MySQL, imati otvoren log i zavrtit cekalicu
		do {
			$iHeader	= fgets(STDIN, 3);
			$aLength 	= unpack("n", $iHeader);
			$iLength	= $aLength["1"];
			if($iLength > 0) {
				// ovo znaci da smo nesto dobili
				$sData = fgets(STDIN, $iLength + 1);
				$this->writeDebugLog("[debug] received data: ". $sData);
				$aCommand = explode(":", $sData);
				if (is_array($aCommand)){
					switch ($aCommand[0]){
						case "isuser":
							// provjeravamo je li korisnik dobar
							if (!isset($aCommand[1])){
								$this->writeLog("[exAuth] invalid isuser command, no username given");
								fwrite(STDOUT, pack("nn", 2, 0));
							} else {
								// ovdje provjeri je li korisnik OK
								$this->dbverify();
								$sUser = str_replace(array("%20", "(a)"), array(" ", "@"), $aCommand[1]);
								$this->writeDebugLog("[debug] checking isuser for ". $sUser);
								$sQuery = "select * from ". $this->sb1gMailPrefix ."users where email='".$this->oMySQL->escape_string($sUser)."@".$this->oMySQL->escape_string($aCommand[2])."'";
								$this->writeDebugLog("[debug] using query ". $sQuery);
								if ($oResult = $this->oMySQL->query($sQuery)){
									if ($oResult->num_rows) {
										// Erfolgreich
										$this->writeLog("[exAuth] valid user: ". $sUser);
										fwrite(STDOUT, pack("nn", 2, 1));
									} else {
										// Nicht erfolgreich
										$this->writeLog("[exAuth] invalid user: ". $sUser);
										fwrite(STDOUT, pack("nn", 2, 0));
									}
									$oResult->close();
								} else {
									$this->writeLog("[MySQL] invalid query: ". $sQuery);
									fwrite(STDOUT, pack("nn", 2, 0));
								}
							}
						break;
						case "auth":
							// provjeravamo autentifikaciju korisnika
							if (sizeof($aCommand) != 4){
								$this->writeLog("[exAuth] invalid auth command, data missing");
								fwrite(STDOUT, pack("nn", 2, 0));
							} else {
								// Anmeldung ueberpruefen
								$this->dbverify();
								$sUser = str_replace(array("%20", "(a)"), array(" ", "@"), $aCommand[1]);
								$this->writeDebugLog("[debug] doing auth for ". $sUser);
								
		
								$sQuery1 = "select * from ". $this->sb1gMailPrefix ."users where email='".$this->oMySQL->escape_string($sUser)."@".$this->oMySQL->escape_string($aCommand[2])."'";
								$this->writeDebugLog("[debug] using query 1 ". $sQuery1);
								
								if ($oResult = $this->oMySQL->query($sQuery1)) {
								  $salt_passwort = mysqli_fetch_array($oResult, MYSQLI_BOTH);
								  $this->writeDebugLog("[debug] salt is: ". $salt_passwort['passwort_salt']);
								  $md5_passwort = md5(md5($aCommand[3]).$salt_passwort['passwort_salt']).
								  $this->writeDebugLog("[debug] salt password is: ". $md5_passwort);
								  $sQuery = "select * from ". $this->sb1gMailPrefix ."users where passwort='". $this->oMySQL->escape_string($md5_passwort) ."' and email='".$this->oMySQL->escape_string($sUser)."@".$this->oMySQL->escape_string($aCommand[2])."'";
								
								  $this->writeDebugLog("[debug] using query ". $sQuery);
								  if ($oResult = $this->oMySQL->query($sQuery)){
									  if ($oResult->num_rows) {
										  // Erfolgreich
										  $this->writeLog("[exAuth] authentificated user ". $sUser ."@". $aCommand[2]);
										  fwrite(STDOUT, pack("nn", 2, 1));
									  } else {
										  // Nicht erfolgreich
										  $this->writeLog("[exAuth] authentification failed for user ". $sUser ."@". $aCommand[2]);
										  fwrite(STDOUT, pack("nn", 2, 0));
									  }
									  $oResult->close();
								  } else {
									  $this->writeLog("[MySQL] invalid query: ". $sQuery);
									  fwrite(STDOUT, pack("nn", 2, 0));
								  }
								  }
							  else {
								  // Nicht erfolgreich
								  $this->writeLog("[exAuth] authentification failed for user ". $sUser ."@". $aCommand[2]);
								  fwrite(STDOUT, pack("nn", 2, 0));
							    }
							}
						break;
						case "setpass":
							// Passwort aendern ist momentan nicht aktiv
							$this->writeLog("[exAuth] setpass command disabled");
							fwrite(STDOUT, pack("nn", 2, 0));
						break;
						default:
							$this->writeLog("[exAuth] unknown command ". $aCommand[0]);
							fwrite(STDOUT, pack("nn", 2, 0));
						break;
					}
				} else {
					$this->writeDebugLog("[debug] invalid command string");
					fwrite(STDOUT, pack("nn", 2, 0));
				}
			}
			unset ($iHeader);
			unset ($aLength);
			unset ($iLength);
			unset($aCommand);
		} while (true);
	}

	public function __destruct()
	{
		// Logdatei schliessen
		$this->writeLog("[exAuth] stop");
		
		if (is_resource($this->rLogFile)){
			fclose($this->rLogFile);
		}
		// MySQL beenden
		if (is_object($this->oMySQL)){
			$this->oMySQL->close();
		}
		
	}

	private function writeLog($sMessage)
	{
		if (is_resource($this->rLogFile)) {
			fwrite($this->rLogFile, date("r") ." ". $sMessage ."\n");
		}
	}

	private function writeDebugLog($sMessage)
	{
		if ($this->bDebug){
			$this->writeLog($sMessage);
		}
	}

	private function dbconnect(){
		if (!is_object($this->oMySQL)){
			$this->oMySQL = new mysqli($this->sDBHost, $this->sDBUser, $this->sDBPassword, $this->sDBName);
			if (mysqli_connect_errno()) {
				$this->writeLog(sprintf("[MySQL] connection failed: %s\n", mysqli_connect_error()));
				$this->writeLog("[exAuth] killing");
				exit();
			} else {
				$this->writeLog("[MySQL] connected");
			}
		}
	}

	private function dbverify(){
		if (!is_object($this->oMySQL)){
			$this->dbconnect();
		} else {
			if (!$this->oMySQL->ping()){
				unset($this->oMySQL);
				$this->writeLog("[MySQL] connection died, reconnecting");
				$this->dbconnect();
			}
		}
	}
}
?>
