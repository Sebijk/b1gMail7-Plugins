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

if (!defined('B1GMAIL_INIT')) {
    die('Directly calling this file is not supported');
}

define('HTTPMAIL_DATE_FORMAT', 'Y-m-d\\TH:i:s');

/**
 * HTTPMail response builder class.
 */
class HTTPMail_ResponseBuilder
{
    private $_encoding;
    private $_param;
    private $_out;
    private $_uri;

    /**
     * constructor.
     *
     * @param array  $param
     * @param string $encoding
     * @param string $uri
     *
     * @return HTTPMail_ResponseBuilder
     */
    public function __construct($param, $encoding, $uri)
    {
        $this->_param = $param;
        $this->_encoding = $encoding;
        $this->_uri = $uri;
    }

    /**
     * generate response.
     *
     * @param string $type
     *
     * @return string
     */
    public function Response($type)
    {
        $this->_out = '<?xml version="1.0" encoding="'.$this->_encoding.'"?>'."\r\n";
        $this->_out .= '<D:multistatus xmlns:D="DAV:" xmlns:m="urn:schemas:mailheader:" xmlns:hm="urn:schemas:httpmail:" xmlns:c="urn:schemas:contacts:" xmlns:h="http://schemas.microsoft.com/hotmail/">'."\r\n";

        if ($type == 'propfind') {
            foreach ($this->_param as $param) {
                $this->_out .= '	<D:response>'."\r\n";
                $this->_out .= '		<D:href>'.$param['_href'].'</D:href>'."\r\n";
                $this->_out .= '		<D:propstat>'."\r\n";
                $this->_out .= '			<D:prop>'."\r\n";
                foreach ($param as $key => $val) {
                    if ($key != '_href') {
                        if (trim($val) == '') {
                            $this->_out .= '				<'.XMLEncode($key).'/>'."\r\n";
                        } else {
                            $this->_out .= '				<'.XMLEncode($key).'>'.XMLEncode($val).'</'.$key.'>'."\r\n";
                        }
                    }
                }
                $this->_out .= '			</D:prop>'."\r\n";
                $this->_out .= '			<D:status>HTTP/1.1 200 OK</D:status>'."\r\n";
                $this->_out .= '		</D:propstat>'."\r\n";
                $this->_out .= '	</D:response>'."\r\n";
            }
        } elseif ($type == 'move') {
            foreach ($this->_param as $param) {
                $this->_out .= '	<D:response>'."\r\n";
                foreach ($param as $key => $val) {
                    if ($key != '_href') {
                        if (trim($val) == '') {
                            $this->_out .= '		<'.XMLEncode($key).'/>'."\r\n";
                        } else {
                            $this->_out .= '		<'.XMLEncode($key).'>'.XMLEncode($val).'</'.$key.'>'."\r\n";
                        }
                    }
                }
                $this->_out .= '		<D:status>HTTP/1.1 200 OK</D:status>'."\r\n";
                $this->_out .= '	</D:response>'."\r\n";
            }
        }

        $this->_out .= '</D:multistatus>';

        // debug?
        if (DEBUG) {
            if ($fp = fopen(B1GMAIL_DIR.'logs/httpmail.log', 'a')) {
                fwrite($fp, sprintf("[%s] HTTPMail response:\n%s\n\n\n",
                    date('r'),
                    $this->_out));
                fclose($fp);
            }
        }

        return $this->_out;
    }
}

/**
 * HTTPMail input parser class.
 */
class HTTPMail_InputParser
{
    private $_input;
    private $_array;
    private $_current;
    private $_p;

    /**
     * constructor.
     *
     * @param string $input
     *
     * @return HTTPMail_InputParser
     */
    public function HTTPMail_InputParser(&$input)
    {
        $this->_input = $input;
        $this->_array = [];
        $this->_p = [];
    }

    /**
     * expat startElement callback.
     *
     * @param resource $parser
     * @param string   $name
     * @param array    $attrs
     */
    private function _startElement($parser, $name, $attrs)
    {
        $this->_p[] = &$this->_current;
        if (isset($this->_current[$name])) {
            $name = $name.count($this->_current);
        }

        $this->_current[$name] = [];
        $this->_current = &$this->_current[$name];

        if (count($attrs) > 0) {
            $this->_current['attrs'] = $attrs;
        }
    }

    /**
     * expart endElement callback.
     *
     * @param resource $parser
     * @param string   $name
     */
    private function _endElement($parser, $name)
    {
        $this->_current = &$this->_p[count($this->_p) - 1];
    }

    /**
     * expat characterData callback.
     *
     * @param resource $parser
     * @param string   $data
     */
    private function _characterData($parser, $data)
    {
        if (trim($data) != '') {
            $this->_current['data'] = $data;
        }
    }

    /**
     * parse the input.
     *
     * @return array
     */
    public function Parse()
    {
        $this->_current = &$this->_array;

        $parser = xml_parser_create();
        xml_set_object($parser, $this);
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
        xml_set_element_handler($parser, '_startElement', '_endElement');
        xml_set_character_data_handler($parser, '_characterData');
        xml_parse($parser, $this->_input, true);
        xml_parser_free($parser);

        unset($this->_p);
        unset($this->_current);

        return $this->_array;
    }
}

/**
 * HTTPMail server class.
 */
class HTTPMail_Server
{
    public $_input;
    public $_uri;
    public $_method;
    public $_signatur;
    public $_self;
    public $_self_url;
    public $_self_uri;
    public $_encoding;
    public $_crlf;
    public $_user;
    public $_pass;

    //
    // functions to be overridden
    //
    public function CheckLogin()
    {
        return true;
    }

    public function Handler_Send()
    {
        return true;
    }

    public function Handler_Post($id)
    {
        return true;
    }

    public function Handler_FolderList($r)
    {
        return [];
    }

    public function Handler_Folder($r, $folder)
    {
        return [];
    }

    public function Handler_Message($id)
    {
        return [];
    }

    public function Handler_Move($id, $dest_folder)
    {
        return true;
    }

    public function Handler_Rename($id, $titel)
    {
        return true;
    }

    public function Handler_Delete($id)
    {
        return true;
    }

    public function Handler_DeleteFolder($id)
    {
        return true;
    }

    public function Handler_NewFolder($titel)
    {
        return 0;
    }

    public function Handler_Read($id, $gelesen)
    {
        return true;
    }

    public function Handler_Copy($id, $dest_folder)
    {
        return true;
    }

    //
    // code
    //

    /**
     * constructor.
     *
     * @param string $self    My filename
     * @param string $selfurl My URL
     *
     * @return HTTPMail_Server
     */
    public function HTTPMail_Server($self, $selfurl)
    {
        global $currentCharset;

        // paths
        $this->_self = $self;
        if (substr($selfurl, -1) != '/') {
            $this->_self_url = $selfurl.'/interface/';
        } else {
            $this->_self_url = $selfurl.'interface/';
        }

        // encoding
        $this->_encoding = $currentCharset;

        // line feed
        $this->_crlf = "\r\n";

        // URLs
        $this->_uri = $_SERVER['REQUEST_URI'];
        $this->_method = $_SERVER['REQUEST_METHOD'];
        $this->_self_uri = $this->_self_url.substr($this->_uri, strpos($this->_uri, $this->_self));

        // read HTTP input
        if ($this->_method != 'POST') {
            $fp = fopen('php://input', 'r');
            while (!feof($fp)) {
                $this->_input .= rtrim(fgets2($fp))."\r\n";
            }
            fclose($fp);
        }

        // check login!
        $this->_get_login();
        if (!$this->CheckLogin()) {
            header('WWW-Authenticate: Basic realm="HTTPMail"');
            header('HTTP/1.0 401 Unauthorized');

            $this->_raise_error('401', 'Unauthorized', true);
            exit();
        }
        $this->_set_cookie();
    }

    /**
     * start handler.
     *
     * @param array $r Request
     *
     * @return array
     */
    public function Handler_Start($r)
    {
        $result = [];
        $result[0] = [];
        $return = &$result[0];
        $return['_href'] = $this->_self_uri;

        foreach ($r as $val) {
            switch (strtoupper($val)) {
            case 'H:ADBAR':
            case 'HM:CONTACTS':
                $return[$val] = '';
                break;
            case 'HM:INBOX':
                $return[$val] = $this->_self_url.$this->_self.'?/folders/'.FOLDER_INBOX.'/';
                break;
            case 'HM:SENDMSG':
                $return[$val] = $this->_self_url.$this->_self.'?/folders/sendmsg/';
                break;
            case 'HM:DRAFTS':
                $return[$val] = $this->_self_url.$this->_self.'?/folders/'.FOLDER_DRAFTS.'/';
                break;
            case 'HM:JUNKEMAIL':
                $return[$val] = $this->_self_url.$this->_self.'?/folders/'.FOLDER_SPAM.'/';
                break;
            case 'HM:SENTITEMS':
                $return[$val] = $this->_self_url.$this->_self.'?/folders/'.FOLDER_OUTBOX.'/';
                break;
            case 'HM:DELETEDITEMS':
                $return[$val] = $this->_self_url.$this->_self.'?/folders/'.FOLDER_TRASH.'/';
                break;
            case 'HM:MSGFOLDERROOT':
                $return[$val] = $this->_self_url.$this->_self.'?/folders/';
                break;
            case 'H:MAXPOLL':
                $return[$val] = 30;
                break;
            case 'H:SIG':
                $return[$val] = XMLEncode($this->_signatur);
                break;
            default:
                $return[$val] = '';
                break;
            }
        }

        return $result;
    }

    /**
     * process client request.
     */
    public function ProcessRequest()
    {
        // debug logging
        PutLog(sprintf('HTTPMail request: <%s %s>',
            $this->_method,
            $this->_uri),
            PRIO_DEBUG,
            __FILE__,
            __LINE__);

        // parse input
        if ($this->_method != 'POST') {
            $input = $this->_parse_input();
        }
        $reg = [];

        // debug?
        if (DEBUG) {
            if ($fp = fopen(B1GMAIL_DIR.'logs/httpmail.log', 'a')) {
                fwrite($fp, sprintf("%s\n[%s] HTTPMail request: %s %s\n%s\n%s\n\n",
                    str_repeat('-', 75),
                    date('r'),
                    $this->_method,
                    $this->_uri,
                    print_r($_SERVER, true),
                    $this->_input));
                fclose($fp);
            }
        }

        // what to do?
        switch ($this->_method) {
        case 'COPY':
            if (preg_match("/\/folders\/([a-zA-Z0-9\-]*)\/([0-9]*)/", $this->_uri, $reg)) {
                $id = $reg[2];
                $dest = $_SERVER['HTTP_DESTINATION'];

                if (preg_match("/\/folders\/([a-zA-Z0-9\-]*)\/([0-9]*)/", $dest, $reg2)) {
                    $dest_folder = $reg2[1];
                    if ($this->Handler_Copy($id, $dest_folder)) {
                        header('HTTP/1.1 201 Created');
                        header('Location: '.$this->_self_url.$this->_self.'?/folders/'.$dest_folder.'/'.$id.'/');
                    } else {
                        header('HTTP/1.1 403 Forbidden');
                    }
                    exit();
                }
            }
            break;

        case 'BCOPY':
            $dest = $_SERVER['HTTP_DESTINATION'];
            if (preg_match("/\/folders\/([a-zA-Z0-9\-]*)\/([0-9]*)/", $dest, $reg)) {
                $dest_folder = $reg[1];
                foreach ($input['D:copy']['D:target'] as $key => $val) {
                    if (substr($key, 0, strlen('D:href')) == 'D:href') {
                        $id = preg_replace('/([^0-9]*)/', '', $val['data']);
                        $this->Handler_Copy($id, $dest_folder);
                    }
                }
                header('HTTP/1.1 201 Created');
            }
            break;

        case 'PROPPATCH':
            if (preg_match("/\/folders\/([a-zA-Z0-9\-]*)\/([0-9]*)/", $this->_uri, $reg)) {
                $id = $reg[2];
                $this->Handler_Read($id, $input['D:propertyupdate']['D:set']['D:prop']['hm:read']['data'] == 1);
            }
            break;

        case 'BPROPPATCH':
            if (preg_match("/\/folders\/([a-zA-Z0-9\-]*)\/([0-9]*)/", $this->_uri, $reg)) {
                $id = $reg[2];
                foreach ($input['D:propertyupdate']['D:target'] as $key => $val) {
                    if (substr($key, 0, strlen('D:href')) == 'D:href') {
                        $id = preg_replace('/([^0-9]*)/', '', $val['data']);
                        $this->Handler_Read($id, $input['D:propertyupdate']['D:target']['D:set']['D:prop']['hm:read']['data'] == 1);
                    }
                }
                header('HTTP/1.1 201 Created');
            }
            break;

        case 'PROPFIND':
            if (substr($this->_uri, -(strlen($this->_self))) == $this->_self) {
                // called without parameters
                $requested = [];
                foreach ($input['D:propfind']['D:prop'] as $key => $val) {
                    $requested[] = $key;
                }
                $this->_build_response($this->Handler_Start($requested), 'propfind');
            } elseif (substr($this->_uri, -strlen('?/folders/')) == '?/folders/' || substr($this->_uri, -strlen('?/folders')) == '?/folders') {
                // folder list
                $requested = [];
                foreach ($input['D:propfind']['D:prop'] as $key => $val) {
                    $requested[] = $key;
                }
                $this->_build_response($this->Handler_Folderlist($requested), 'propfind');
            } elseif (preg_match("/\/folders\/([a-zA-Z0-9\-]*)/", $this->_uri, $reg)) {
                // folder list
                $requested = [];
                foreach ($input['D:propfind']['D:prop'] as $key => $val) {
                    $requested[] = $key;
                }
                $this->_build_response($this->Handler_Folder($requested, $reg[1]), 'propfind');
            }
            break;

        case 'GET':
            if (preg_match("/\/folders\/([a-zA-Z0-9\-]*)\/([0-9]*)/", $this->_uri, $reg)) {
                $id = $reg[2];

                header('Cache-Control: no-cache');
                header('Pragma: no-cache');
                header('Expires: Mon, 01 Jan 1999 00:00:00 GMT');
                header('Content-Type: message/rfc822');
                $this->Handler_Message($id);

                exit();
            }
            break;

        case 'DELETE':
            if (preg_match("/\/folders\/([a-zA-Z0-9\-]*)\/([0-9]{1,20})/", $this->_uri, $reg)) {
                $id = $reg[2];
                $this->Handler_Delete($id);
            } elseif (preg_match("/\/folders\/([0-9\-]*)/", $this->_uri, $reg)) {
                $id = $reg[1];
                $this->Handler_DeleteFolder($id);
            }

            header('HTTP/1.1 201 Created');
            exit();
            break;

        case 'MKCOL':
            if (preg_match("/([^\/]*)([\/]*)$/", $this->_uri, $reg)) {
                $folder = addslashes(urldecode($reg[1]));
                $id = $this->Handler_NewFolder($folder);

                header('HTTP/1.1 201 Created');
                header('Location: '.$this->_self_url.$this->_self.'?/folders/'.$id.'/');
                exit();
            }
            break;

        case 'BDELETE':
            foreach ($input['D:delete']['D:target'] as $key => $val) {
                $id = preg_replace('/([^0-9]*)/', '', $val['data']);
                $this->Handler_Delete($id);
            }
            header('HTTP/1.1 201 Created');
            exit();
            break;

        case 'BMOVE':
            preg_match("/\/folders\/([a-zA-Z0-9\-]*)/", $this->_uri, $reg);
            $f = $reg[1];

            $dest = $_SERVER['HTTP_DESTINATION'];
            $response = [];
            if (preg_match("/\/folders\/([a-zA-Z0-9\-]*)/", $dest, $reg)) {
                foreach ($input['D:move']['D:target'] as $key => $val) {
                    $id = preg_replace('/([^0-9]*)/', '', $val['data']);
                    $this->Handler_Move($id, $reg[1]);
                    $response[] = [
                        'D:href' => $this->_self_url.$this->_self.'?/folders/'.$f.'/'.$id.'/',
                        'D:location' => $this->_self_url.$this->_self.'?/folders/'.$reg[1].'/'.$id.'/',
                    ];
                }

                $this->_build_response($response, 'move');
                exit();
            }
            break;

        case 'MOVE':
            if (preg_match("/\/folders\/([a-zA-Z0-9\-]*)\/([0-9]*)\//", $this->_uri, $reg)) {
                $folder = $reg[1];
                $id = $reg[2];
                $dest = $_SERVER['HTTP_DESTINATION'];

                if (preg_match("/\/folders\/([a-zA-Z0-9\-]*)\/([0-9]*)\//", $dest, $reg2)) {
                    $dest_folder = $reg2[1];

                    $this->Handler_Move($id, $dest_folder);

                    header('HTTP/1.1 201 Created');
                    header('Location: '.$this->_self_url.$this->_self.'?/folders/'.$dest_folder.'/'.$id.'/');
                    exit();
                }
            } elseif (preg_match("/\/folders\/([^\/]*)/", $this->_uri, $reg)) {
                $id = $reg[1];
                $dest = $_SERVER['HTTP_DESTINATION'];

                if (preg_match("/\/folders\/([^\/]*)/", $dest, $reg2)) {
                    $new_name = addslashes(urldecode($reg2[1]));

                    $this->Handler_Rename($id, $new_name);

                    header('HTTP/1.1 201 Created');
                    header('Location: '.$self.'httpmail.php?/folders/'.$id.'/');
                    exit();
                }
            }
            break;

        case 'POST':
            if (strtolower(substr($this->_uri, -9)) == '/sendmsg/' || strtolower(substr($this->_uri, -8)) == '/sendmsg') {
                if ($this->Handler_Send()) {
                    header('HTTP/1.1 201 Created');
                } else {
                    header('HTTP/1.1 403 Forbidden');
                }
                exit();
            } elseif (preg_match("/\/folders\/([^\/]*)/", $this->_uri, $reg)) {
                $id = $reg[1];
                if ($this->Handler_Post($id)) {
                    header('HTTP/1.1 201 Created');
                    header('Location: '.$self.'httpmail.php?/folders/'.$id.'/');
                } else {
                    header('HTTP/1.1 403 Forbidden');
                }
                exit();
            }
            break;
        }

        $this->_raise_error('500', 'Don\'t know what to do', true);
    }

    /**
     * build response.
     *
     * @param array  $param
     * @param string $type
     */
    private function _build_response($param, $type)
    {
        $this->_self_uri;

        $this->_headers();
        $builder = _new('HTTPMail_ResponseBuilder', [$param, $this->_encoding, $this->_self_uri]);
        $response = $builder->Response($type);
        unset($builder);
        echo $response;
        exit();
    }

    /**
     * parse input.
     *
     * @return array
     */
    private function _parse_input()
    {
        $parser = _new('HTTPMail_InputParser', [$this->_input]);

        return $parser->Parse();
    }

    /**
     * create error message.
     *
     * @param int    $code
     * @param string $message
     * @param bool   $fatal
     */
    private function _raise_error($code, $message, $fatal = false)
    {
        DisplayError(0x16,
            'HTTPMail error',
            'An error occured while trying to process the HTTPMail request.',
            sprintf("Error code:\n%d\n\nError message:\n%s\n\nFatal:\n%s",
                $code,
                $message,
                $fatal ? 'Yes' : 'No'),
            __FILE__,
            __LINE__);
        if ($fatal) {
            exit();
        }
    }

    /**
     * set HTTPMail cookie.
     */
    private function _set_cookie()
    {
        setcookie('httpmail_auth', base64_encode($this->_user).':'.base64_encode($this->_pass), time());
    }

    /**
     * get login.
     */
    private function _get_login()
    {
        if (!isset($_COOKIE['httpmail_auth'])) {
            if (isset($_SERVER['PHP_AUTH_USER'])) {
                $sess_user = $_SERVER['PHP_AUTH_USER'];
                $sess_pw = $_SERVER['PHP_AUTH_PW'];
            } elseif (isset($_SERVER['HTTP_AUTHORIZATION']) && substr(strtolower($_SERVER['HTTP_AUTHORIZATION'], 0, 5)) == 'basic') {
                list($sess_user, $sess_pw) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
            }
        } else {
            list($user, $pw) = explode(':', $_COOKIE['httpmail_auth']);
            $sess_user = base64_decode($user);
            $sess_pw = base64_decode($pw);
        }

        if (isset($sess_user) && isset($sess_pw)) {
            $this->_user = $sess_user;
            $this->_pass = $sess_pw;
        }
    }

    /**
     * send headers.
     */
    private function _headers()
    {
        global $currentCharset;

        header('HTTP/1.1 207 Multi-status');
        header('Server: b1gMail/'.B1GMAIL_VERSION);
        header('P3P:CP="BUS CUR CONo FIN IVDo ONL OUR PHY SAMo TELo"');
        header('Content-Type: text/xml; charset='.$currentCharset);
        header('Pragma: no-cache');
        header('Cache-Control: no-cache');
        header('Connection: close');
        header('X-Dav-Error: 200 No error');
    }
}
