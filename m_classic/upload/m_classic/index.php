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

/*
 * default action = login
 */
if (!isset($_REQUEST['action'])) {
    if (isset($_COOKIE['bm_savedUser'])) {
        $password = $_COOKIE['bm_savedPassword'];
        $email = $_COOKIE['bm_savedUser'];
        $language = $_COOKIE['bm_savedLanguage'];
        list($result, $param) = BMUser::Login($email, $password);

        if ($result == USER_OK) {
            // stats
            Add2Stat('mobile_login');

            // register language
            $_SESSION['bm_sessionLanguage'] = $language;

            // set cookies
            setcookie('bm_savedUser', $email, time() + TIME_ONE_YEAR);
            setcookie('bm_savedPassword', $password, time() + TIME_ONE_YEAR);
            setcookie('bm_savedLanguage', $language, time() + TIME_ONE_YEAR);
            // redirect to target page
            header('Location: main.php?sid='.$param);
            exit();
        } else {
            $tpl->assign('page', 'm_classic/login.tpl');
        }
    } else {
        $_REQUEST['action'] = 'login';
    }
}

/*
 * login
 */
if ($_REQUEST['action'] == 'login') {
    if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'login') {
        // get login
        $password = $_REQUEST['password'];
        $email = $_REQUEST['email'];
        $language = $bm_prefs['language'];
        $savelogin = $_REQUEST['savelogin'];

        // login
        list($result, $param) = BMUser::Login($email, $password);

        // login ok?
        if ($result == USER_OK) {
            // stats
            Add2Stat('mobile_login');

            // register language
            $_SESSION['bm_sessionLanguage'] = $language;
            // set cookies
            if ($savelogin == true) {
                setcookie('bm_savedUser', $email, time() + TIME_ONE_YEAR);
                setcookie('bm_savedPassword', $password, time() + TIME_ONE_YEAR);
                setcookie('bm_savedLanguage', $language, time() + TIME_ONE_YEAR);
            }

            // redirect to target page
            header('Location: main.php?sid='.$param);
            exit();
        } else {
            // tell user what happened
            switch ($result) {
            case USER_BAD_PASSWORD:
                $tpl->assign('msg', sprintf($lang_user['badlogin'], $param));
                break;
            case USER_DOES_NOT_EXIST:
                $tpl->assign('msg', $lang_user['baduser']);
                break;
            case USER_LOCKED:
                $tpl->assign('msg', $lang_user['userlocked']);
                break;
            case USER_LOGIN_BLOCK:
                $tpl->assign('msg', sprintf($lang_user['loginblocked'], FormatDate($param)));
                break;
            }
            $tpl->assign('page', 'm_classic/message.tpl');
        }
    } else {
        $tpl->assign('page', 'm_classic/login.tpl');
    }

    // assign
    $tpl->assign('pageTitle', $bm_prefs['titel']);
    $tpl->display('m_classic/index.tpl');
}
