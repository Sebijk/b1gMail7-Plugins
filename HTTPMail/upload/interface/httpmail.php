<?php
/*
 * b1gMail
 * (c) 2021 Patrick Schlangen et al
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 */

define('INTERFACE_MODE', true);
define('DEBUG', true);
include '../serverlib/init.inc.php';
include '../serverlib/mailbox.class.php';
include '../serverlib/httpmail_server.class.php';

/**
 * HTTPMail interface implementation.
 */
class BMHTTPMail_Server extends HTTPMail_Server
{
    private $_userObject;
    private $_userRow;
    private $_groupObject;
    private $_groupRow;
    private $_mailbox;
    private $_folders;
    private $_httpmailallow;

    /**
     * check user login, initiate session.
     *
     * @return bool
     */
    public function CheckLogin()
    {
        global $db;
        if (empty($this->_user) || empty($this->_pass)) {
            return false;
        }

        // login
        list($result, $userID) = BMUser::Login($this->_user, $this->_pass, false, false);

        // login OK?
        if ($result == USER_OK) {
            // get user and group
            $this->_userObject = _new('BMUser', [$userID]);
            $this->_groupObject = $this->_userObject->GetGroup();
            $this->_userRow = $this->_userObject->Fetch();
            $this->_groupRow = $this->_groupObject->Fetch();
            $this->_signatur = $this->_groupRow['signatur'];
            $res = $db->Query('SELECT value FROM {pre}groupoptions WHERE gruppe=? AND module=? AND `key`=?',
			$this->_groupRow['id'],
			'HTTPMailPlugin',
			'httpmail');
            $row = $res->FetchArray();
            $this->_httpmailallow = $row[0];

            // check privileges
            if ($this->_httpmailallow == 1) {
                $this->_mailbox = _new('BMMailbox', [$this->_userRow['id'], $this->_userRow['email'], $this->_userObject]);
                $this->_folders = $this->_mailbox->GetFolderList(false, false);

                return true;
            } else {
                // log
                PutLog(sprintf('HTTPMail login as <%s> failed (disallowed by group settings)',
                    $this->_user),
                    PRIO_NOTE,
                    __FILE__,
                    __LINE__);

                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * send handler.
     *
     * @return bool
     */
    public function Handler_Send()
    {
        global $bm_prefs;

        // send limit
        if (($this->_userRow['last_send'] + $this->_groupRow['send_limit_time']) > time()) {
            return false;
        }

        // get MAIL FROM / RCPT TO
        $message = '';
        $mailSender = '';
        $mailRecipients = [];
        $fp = fopen('php://input', 'r');
        while (!feof($fp)) {
            $line = rtrim(fgets2($fp), "\r\n");

            if (substr($line, 0, 10) == 'MAIL FROM:') {
                $mailSender = ExtractMailAddress($line);
            } elseif (substr($line, 0, 8) == 'RCPT TO:') {
                $mailRecipients[] = ExtractMailAddress($line);
            }

            if (trim($line) == '') {
                break;
            }
        }

        // check
        if ($mailSender == ''
            || count($mailRecipients) == 0
            || count($mailRecipients) > $this->_groupRow['max_recps']) {
            PutLog(sprintf('HTTPMail send failed (mailSender: %s, mailRecipients: %d)',
                $mailSender,
                count($mailRecipients)),
                PRIO_NOTE,
                __FILE__,
                __LINE__);

            return false;
        }

        // check if sender is allowed
        $senderAddresses = $this->_userObject->GetPossibleSenders();
        
        $senderOK = false;
        foreach ($senderAddresses as $senderAddress) {
            if (strtolower($mailSender) == strtolower(ExtractMailAddress($senderAddress))) {
                $senderOK = true;
                break;
            }
        }
        if (!$senderOK) {
            PutLog(sprintf('HTTPMail send failed (mailSender <%s> not allowed)',
                $mailSender),
                PRIO_NOTE,
                __FILE__,
                __LINE__);

            return false;
        }

        // check if recipients are blocked
        $blockedRecipients = [];
        foreach ($mailRecipients as $recp) {
            if (RecipientBlocked($recp)) {
                $blockedRecipients[] = $recp;
            }
        }

        // blocked recipients?
        if (count($blockedRecipients) > 0) {
            PutLog(sprintf('HTTPMail send failed (mailSender: %s, mailRecipients: %d, blocked recipients: %d)',
                $mailSender,
                count($mailRecipients),
                count($blockedRecipients)),
                PRIO_NOTE,
                __FILE__,
                __LINE__);

            return false;
        }

        // read message
        $tempFileID = RequestTempFile($this->_userRow['id']);
        $tempFileName = TempFileName($tempFileID);
        $messageFP = fopen($tempFileName, 'wb+');
        while (!feof($fp)) {
            $line = rtrim(fgets2($fp), "\r\n");
            fwrite($messageFP, $line."\r\n");
        }
        fclose($fp);

        // parse message
        fseek($messageFP, 0, SEEK_SET);
        $mailObj = _new('BMMail', [0, false, $messageFP, false]);
        $mailObj->Parse();
        $mailObj->ParseInfo();

        // load class, if needed
        if (!class_exists('BMSendMail')) {
            include B1GMAIL_DIR.'serverlib/sendmail.class.php';
        }

        // send
        $sendMail = _new('BMSendMail');
        $sendMail->SetUserID($this->_userRow['id']);
        $sendMail->SetSender($mailSender);
        $sendMail->SetRecipients($mailRecipients);
        $sendMail->SetSubject($mailObj->GetHeaderValue('subject'));
        $sendMail->SetBodyStream($messageFP);

        // send
        fseek($messageFP, 0, SEEK_SET);
        if ($sendMail->Send()) {
            //
            // update stats
            //
            Add2Stat('send');
            $domains = GetDomainList();
            $local = false;
            foreach ($domains as $domain) {
                if (strpos(strtolower(implode(' ', $mailRecipients)), '@'.strtolower($domain)) !== false) {
                    $local = true;
                }
            }
            Add2Stat('send_'.($local ? 'intern' : 'extern'));
            $this->_userObject->UpdateLastSend(count($mailRecipients));

            //
            // add log entry
            //
            PutLog(sprintf('<%s> (%d, IP: %s) sends mail from <%s> to <%s> using HTTPMail',
                $this->_userRow['email'],
                $this->_userRow['id'],
                $_SERVER['REMOTE_ADDR'],
                ExtractMailAddress($mailSender),
                implode('>, <', $mailRecipients)),
                PRIO_NOTE,
                __FILE__,
                __LINE__);

            //
            // save copy
            //
            fseek($messageFP, 0, SEEK_SET);
            $this->_mailbox->StoreMail($mailObj, FOLDER_OUTBOX);

            //
            // clean up
            //
            fclose($messageFP);
            ReleaseTempFile($this->_userRow['id'], $tempFileID);

            return true;
        } else {
            fclose($messageFP);
            ReleaseTempFile($this->_userRow['id'], $tempFileID);

            return false;
        }
    }

    /**
     * post (store) handler.
     *
     * @param int $folderID
     *
     * @return bool
     */
    public function Handler_Post($folderID)
    {
        // get temp file
        $tempFileID = RequestTempFile($this->_userRow['id']);
        $tempFileName = TempFileName($tempFileID);
        $messageFP = fopen($tempFileName, 'wb+');

        // read message
        $fp = fopen('php://input', 'r');
        while (!feof($fp)) {
            $line = rtrim(fgets2($fp), "\r\n");
            fwrite($messageFP, $line."\r\n");
        }
        fclose($fp);

        // parse
        fseek($messageFP, 0, SEEK_SET);
        $mailObj = _new('BMMail', [0, false, $messageFP, false]);
        $mailObj->Parse();
        $mailObj->ParseInfo();

        // store
        fseek($messageFP, 0, SEEK_SET);
        $storeResult = $this->_mailbox->StoreMail($mailObj, $folderID);
        if ($storeResult == STORE_RESULT_OK) {
            //
            // add log entry
            //
            PutLog(sprintf('<%s> (%d) posted mail to folder <%d> using HTTPMail',
                $this->_userRow['email'],
                $this->_userRow['id'],
                $folderID),
                PRIO_NOTE,
                __FILE__,
                __LINE__);
        }

        // clean up
        fclose($messageFP);
        ReleaseTempFile($this->_userRow['id'], $tempFileID);

        // return
        return $storeResult == STORE_RESULT_OK;
    }

    /**
     * delete folder.
     *
     * @param int $id
     *
     * @return bool
     */
    public function Handler_DeleteFolder($id)
    {
        return $this->_mailbox->DeleteFolder((int) $id);
    }

    /**
     * create folder.
     *
     * @param string $title
     *
     * @return int
     */
    public function Handler_NewFolder($title)
    {
        return $this->_mailbox->AddFolder($title, -1, true, -1, false);
    }

    /**
     * rename a folder.
     *
     * @param int    $id
     * @param string $title
     *
     * @return bool
     */
    public function Handler_Rename($id, $title)
    {
        return $this->_mailbox->UpdateFolder((int) $id, $title, FOLDER_INBOX, true, -1, BMLINK_AND);
    }

    /**
     * delete a message.
     *
     * @param int $id
     *
     * @return bool
     */
    public function Handler_Delete($id)
    {
        return $this->_mailbox->DeleteMail((int) $id, true);
    }

    /**
     * move a mail.
     *
     * @param int $id
     * @param int $destFolder
     */
    public function Handler_Move($id, $destFolder)
    {
        return $this->_mailbox->MoveMail((int) $id, (int) $destFolder);
    }

    /**
     * retrieve a mail.
     *
     * @param int $id
     */
    public function Handler_Message($id)
    {
        $mail = $this->_mailbox->GetMail((int) $id);

        if ($mail !== false) {
            // open message
            $messageFP = $mail->GetMessageFP();
            if ($messageFP) {
                // send it
                while (!feof($messageFP)) {
                    echo fread($messageFP, 4096);
                }

                // close
                fclose($messageFP);

                return true;
            }
        }

        return false;
    }

    /**
     * get folder contents.
     *
     * @param array $r        Request
     * @param int   $folderID
     */
    public function Handler_Folder($r, $folderID)
    {
        // fetch full mail list
        $mails = $this->_mailbox->GetMailList((int) $folderID);

        // prepare output
        $result = [];
        $k = 0;
        foreach ($mails as $mailID => $mail) {
            $current = [];

            reset($r);
            $current['_href'] = $this->_self_uri.(substr($this->_self_uri, -1) == '/' ? '' : '/').$mailID.'/';
            foreach ($r as $key) {
                switch (strtoupper($key)) {
                case 'D:ISFOLDER':
                    $current[$key] = 0;
                    break;
                case 'HM:READ':
                    $current[$key] = ($mail['flags'] & FLAG_UNREAD) ? '0' : '1';
                    break;
                case 'M:HASATTACHMENT':
                    $current[$key] = ($mail['flags'] & FLAG_ATTACHMENT) ? '1' : '0';
                    break;
                case 'M:TO':
                    $current[$key] = $mail['to'];
                    break;
                case 'M:FROM':
                    $current[$key] = $mail['from'];
                    break;
                case 'M:SUBJECT':
                    $current[$key] = $mail['subject'];
                    break;
                case 'M:DATE':
                    $current[$key] = gmdate(HTTPMAIL_DATE_FORMAT, $mail['timestamp']);
                    break;
                case 'D:GETCONTENTLENGTH':
                    $current[$key] = $mail['size'];
                    break;
                }
            }

            $result[] = $current;
        }

        // return
        return $result;
    }

    /**
     * set read flag.
     *
     * @param int  $id
     * @param bool $read
     */
    public function Handler_Read($id, $read)
    {
        return $this->_mailbox->FlagMail(FLAG_UNREAD, !$read, (int) $id);
    }

    /**
     * copy a message.
     *
     * @param int $id
     * @param int $destFolder
     */
    public function Handler_Copy($id, $destFolder)
    {
        $mail = $this->_mailbox->GetMail($id);
        if ($mail) {
            $mail->Parse();
            $mail->ParseInfo();
            $storeResult = $this->_mailbox->StoreMail($mail, $destFolder);

            if ($storeResult == STORE_RESULT_OK) {
                //
                // add log entry
                //
                PutLog(sprintf('<%s> (%d) copied mail <%d> to folder <%d> using HTTPMail',
                    $this->_userRow['email'],
                    $this->_userRow['id'],
                    $id,
                    $destFolder),
                    PRIO_NOTE,
                    __FILE__,
                    __LINE__);

                return true;
            }
        }

        return false;
    }

    /**
     * folder list.
     *
     * @param array $r Request
     *
     * @return array
     */
    public function Handler_Folderlist($r)
    {
        // user folders
        $folders = $this->_folders;

        // process folders, get info
        $result = [];
        foreach ($folders as $folderID => $folder) {
            if ($folder['intelligent'] == 1) {
                continue;
            }

            $current = [];
            $specialString = '';
            if ($folderID == FOLDER_INBOX) {
                $specialString = 'inbox';
            } elseif ($folderID == FOLDER_OUTBOX) {
                $specialString = 'sentitems';
            } elseif ($folderID == FOLDER_TRASH) {
                $specialString = 'deleteditems';
            } elseif ($folderID == FOLDER_SPAM) {
                $specialString = 'junkemail';
            } elseif ($folderID == FOLDER_DRAFTS) {
                $specialString = 'drafts';
            }

            reset($r);
            $current['_href'] = $this->_self_url.$this->_self.'?/folders/'.$folderID.'/';
            foreach ($r as $key) {
                switch (strtoupper($key)) {
                case 'D:ISFOLDER':
                    $current[$key] = 1;
                    break;
                case 'D:DISPLAYNAME':
                    $current[$key] = $folderID > 0 || $folderID == FOLDER_SPAM ? $folder['title'] : '';
                    break;
                case 'HM:SPECIAL':
                    $current[$key] = $specialString;
                    break;
                case 'D:HASSUBS':
                    $current[$key] = 0;
                    break;
                case 'D:NOSUBS':
                    $current[$key] = 1;
                    break;
                case 'HM:UNREADCOUNT':
                    $current[$key] = $this->_mailbox->GetMailCount($folderID, true);
                    break;
                case 'D:VISIBLECOUNT':
                    $current[$key] = $this->_mailbox->GetMailCount($folderID);
                    break;
                }
            }

            $result[] = $current;
        }

        return $result;
    }
}

/**
 * debug?
 */
function httpMailErrorHandler($errNo, $errStr, $errFile, $errLine)
{
    if ($fp = fopen(B1GMAIL_DIR.'logs/httpmail.log', 'a')) {
        fwrite($fp, sprintf("\n--\n[%s] PHP error:\n\tNo: %d\n\tStr: %s\n\tFile: %s @ %d\n--\n\n",
            date('r'),
            $errNo,
            $errStr,
            $errFile,
            $errLine));
        fclose($fp);
    }
}
if (DEBUG) {
    set_error_handler('httpMailErrorHandler');
}

/**
 * run!
 */
$srv = _new('BMHTTPMail_Server', [substr(__FILE__, strlen(dirname(__FILE__)) + 1), $bm_prefs['selfurl']]);
$srv->ProcessRequest();
