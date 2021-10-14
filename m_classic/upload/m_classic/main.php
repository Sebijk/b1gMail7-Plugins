<?php
/*
 * b1gMail
 * Copyright (c) 2021 Patrick Schlangen et al
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

include '../serverlib/init.inc.php';
include '../serverlib/mailbox.class.php';
include '../serverlib/mailbuilder.class.php';
RequestPrivileges(PRIVILEGES_USER | PRIVILEGES_MOBILE);

/**
 * open mailbox.
 */
$mailbox = _new('BMMailbox', [$userRow['id'], $userRow['email'], $thisUser]);
$folderList = $mailbox->GetFolderList(true);

/*
 * default action = inbox
 */
if (!isset($_REQUEST['action'])) {
    $_REQUEST['action'] = 'inbox';
}

/*
 * inbox
 */
if ($_REQUEST['action'] == 'inbox') {
    // get folder id (default = inbox)
    $folderID = (isset($_REQUEST['folder']) && isset($folderList[(int) $_REQUEST['folder']]))
                    ? (int) $_REQUEST['folder']
                    : FOLDER_INBOX;
    $folderName = $folderList[$folderID]['title'];

    // page stuff
    $mailsPerPage = $mailbox->GetMailsPerPage($folderID);
    $pageNo = (isset($_REQUEST['page']))
                    ? (int) $_REQUEST['page']
                    : 1;
    $mailCount = $mailbox->GetMailCount($folderID);
    $pageCount = max(1, ceil($mailCount / max(1, $mailsPerPage)));
    $pageNo = min($pageCount, max(1, $pageNo));

    // get mail list
    $mailList = $mailbox->GetMailList($folderID,
                                        $pageNo,
                                        $mailsPerPage);

    // assign
    $tpl->assign('perPage', $mailsPerPage);
    $tpl->assign('pageNo', $pageNo);
    $tpl->assign('pageCount', $pageCount);
    $tpl->assign('folderID', $folderID);
    $tpl->assign('mails', $mailList);
    $tpl->assign('titleIcon', 'li/menu_ico_'.$folderList[$folderID]['type'].'.png');
    $tpl->assign('titleLink', 'main.php?action=folders&sid='.session_id());
    $tpl->assign('pageTitle', $folderName);
    $tpl->assign('page', 'm_classic/folder.tpl');
    $tpl->display('m_classic/index.tpl');
}

/*
 * folders
 */
elseif ($_REQUEST['action'] == 'folders') {
    // assign
    $tpl->assign('folders', $folderList);
    $tpl->assign('pageTitle', $lang_user['folders']);
    $tpl->assign('page', 'm_classic/folders.tpl');
    $tpl->display('m_classic/index.tpl');
}

/*
 * read
 */
elseif ($_REQUEST['action'] == 'read'
        && isset($_REQUEST['id'])) {
    $mail = $mailbox->GetMail((int) $_REQUEST['id']);

    if ($mail !== false) {
        // unread? => mark as read
        if (($mail->flags & FLAG_UNREAD) != 0) {
            $mailbox->FlagMail(FLAG_UNREAD, false, (int) $_REQUEST['id']);
        }

        $textParts = $mail->GetTextParts();
        if (isset($textParts['text'])) {
            $textMode = 'text';
            $text = formatEMailText($textParts['text'], true, true);
        } elseif (isset($textParts['html'])) {
            $textMode = 'html';
            $text = formatEMailHTMLText($textParts['html'],
                true,
                $attachments,
                (int) $_REQUEST['id'],
                true);
        } else {
            $textMode = 'text';
            $text = '';
        }

        // prev & next mail
        list($prevID, $nextID) = $mailbox->GetPrevNextMail($mail->_row['folder'], (int) $_REQUEST['id']);
        if ($prevID != -1) {
            $tpl->assign('prevID', $prevID);
        }
        if ($nextID != -1) {
            $tpl->assign('nextID', $nextID);
        }

        // reply to
        if (($replyTo = $mail->GetHeaderValue('reply-to')) && $replyTo != '') {
            $replyTo = $replyTo;
        } else {
            $replyTo = $mail->GetHeaderValue('from');
        }

        // assign
        $tpl->assign('subject', $mail->GetHeaderValue('subject'));
        $tpl->assign('folderID', $mail->_row['folder']);
        $tpl->assign('mailID', (int) $_REQUEST['id']);
        $tpl->assign('pageTitle', htmlentities(strlen($mail->GetHeaderValue('subject')) > 25
            ? substr($mail->GetHeaderValue('subject'), 0, 23).'...'
            : $mail->GetHeaderValue('subject')));
        $tpl->assign('replyTo', ExtractMailAddress($replyTo));
        $tpl->assign('replySubject', urlencode($userRow['re'].' '.$mail->GetHeaderValue('subject')));
        $tpl->assign('from', $mail->GetHeaderValue('from'));
        $tpl->assign('to', $mail->GetHeaderValue('to'));
        $tpl->assign('cc', $mail->GetHeaderValue('cc'));
        $tpl->assign('date', $mail->date);
        $tpl->assign('text', $text);
        $tpl->assign('page', 'm_classic/read.tpl');
        $tpl->display('m_classic/index.tpl');
    }
}

/*
 * compose
 */
elseif ($_REQUEST['action'] == 'compose') {
    $mail = [];
    if (isset($_REQUEST['to'])) {
        $mail['to'] = $_REQUEST['to'];
    }
    if (isset($_REQUEST['subject'])) {
        $mail['subject'] = $_REQUEST['subject'];
    }

    // assign
    $tpl->assign('mail', $mail);
    $tpl->assign('pageTitle', $lang_user['sendmail']);
    $tpl->assign('page', 'm_classic/compose.tpl');
    $tpl->display('m_classic/index.tpl');
}

/*
 * delete mail
 */
elseif ($_REQUEST['action'] == 'deleteMail'
        && isset($_REQUEST['id'])) {
    $mail = $mailbox->GetMail((int) $_REQUEST['id']);

    if ($mail !== false) {
        $folderID = $mail->_row['folder'];
        $mailbox->DeleteMail($_REQUEST['id']);
        header('Location: main.php?folder='.$folderID.'&sid='.session_id());
    }
}

/*
 * send mail
 */
elseif ($_REQUEST['action'] == 'sendMail') {
    $tpl->assign('backLink', 'main.php?action=compose&sid='.session_id());

    // wait time?
    if (($userRow['last_send'] + $groupRow['send_limit']) > time()) {
        $tpl->assign('msg', sprintf($lang_user['waituntil3'], ($userRow['last_send'] + $groupRow['send_limit']) - time()));
    } else {
        // no recipients?
        $recipients = ExtractMailAddresses($_REQUEST['to'].' '.$_REQUEST['cc']);
        if (count($recipients) > 0) {
            // too much recipients?
            if (count($recipients) > $groupRow['max_recps']) {
                $tpl->assign('msg', sprintf($lang_user['toomanyrecipients'], $groupRow['max_recps'], count($recipients)));
            } else {
                //
                // headers
                //
                $to = $_REQUEST['to'];
                $cc = $_REQUEST['cc'];

                // sender?
                $senderAddresses = $thisUser->GetPossibleSenders();
                $from = $senderAddresses[0];

                // prepare header fields
                $to = trim(str_replace(["\r", "\t", "\n"], '', $to));
                $cc = trim(str_replace(["\r", "\t", "\n"], '', $cc));
                $subject = trim(str_replace(["\r", "\t", "\n"], '', $_REQUEST['subject']));
                $replyTo = $from;

                // build the mail
                $mail = _new('BMMailBuilder');

                // mandatory headers
                $mail->AddHeaderField('X-Sender-IP', $_SERVER['REMOTE_ADDR']);
                $mail->AddHeaderField('From', $from);
                $mail->AddHeaderField('Subject', $subject);
                $mail->AddHeaderField('Reply-To', $replyTo);

                // optional headers
                if ($to != '') {
                    $mail->AddHeaderField('To', $to);
                }
                if ($cc != '') {
                    $mail->AddHeaderField('Cc', $cc);
                }

                //
                // add text
                //
                $mailText = $_REQUEST['text'].GetsigStr('text');
                ModuleFunction('OnSendMail', [&$mailText, false]);
                $mail->AddText($mailText,
                    'plain',
                    $currentCharset);

                //
                // send!
                //
                $outboxFP = $mail->Send();

                //
                // ok?
                //
                if ($outboxFP && is_resource($outboxFP)) {
                    //
                    // update stats
                    //
                    Add2Stat('send');
                    $domains = explode(':', $bm_prefs['domains']);
                    $local = false;
                    foreach ($domains as $domain) {
                        if (strpos(strtolower($to.$cc), '@'.strtolower($domain)) !== false) {
                            $local = true;
                        }
                    }
                    Add2Stat('send_'.($local ? 'intern' : 'extern'));
                    $thisUser->UpdateLastSend(count($recipients));

                    //
                    // add log entry
                    //
                    PutLog(sprintf('<%s> (%d, IP %s) sends mail from <%s> to <%s> using mobile compose form',
                        $userRow['email'],
                        $userRow['id'],
                        $_SERVER['REMOTE_ADDR'],
                        ExtractMailAddress($from),
                        implode('>, <', $recipients)),
                        PRIO_NOTE,
                        __FILE__,
                        __LINE__);

                    //
                    // plugin handler
                    //
                    ModuleFunction('AfterSendMail', [$userRow['id'], ExtractMailAddress($from), $recipients, $outboxFP]);

                    //
                    // save copy
                    //
                    $saveTo = FOLDER_OUTBOX;
                    $mailObj = _new('BMMail', [0, false, $outboxFP, false]);
                    $mailObj->Parse();
                    $mailObj->ParseInfo();
                    $mailbox->StoreMail($mailObj, $saveTo);

                    //
                    // clean up
                    //
                    $mail->CleanUp();

                    //
                    // done
                    //
                    $tpl->assign('msg', $lang_user['mailsent']);
                    $tpl->assign('backLink', 'main.php?sid='.session_id());
                } else {
                    $tpl->assign('msg', $lang_user['sendfailed']);
                }
            }
        } else {
            $tpl->assign('msg', $lang_user['norecipients']);
        }
    }

    // assign
    $tpl->assign('page', 'm_classic/message.tpl');
    $tpl->assign('pageTitle', $lang_user['sendmail']);
    $tpl->display('m_classic/index.tpl');
}

/*
 * logout
 */
elseif ($_REQUEST['action'] == 'logout') {
    // delete cookies
    setcookie('bm_savedUser', '', time() - TIME_ONE_HOUR);
    setcookie('bm_savedPassword', '', time() - TIME_ONE_HOUR);
    setcookie('bm_savedLanguage', '', time() - TIME_ONE_HOUR);
    setcookie('bm_savedSSL', '', time() - TIME_ONE_HOUR);
    BMUser::Logout();
    header('Location: ./index.php');
    exit();
}
