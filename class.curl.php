<?php
//include_once 'class.proxy.php';

class curl
{

    var $callback = false;
        var $cookie;

        //curl_setopt settings
        var $useragent;
        var $useragents = array(
            'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT)',
            'Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.4) Gecko/20050511 Firefix/1.0.4',
            'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.6) Gecko/20040206 Firefox/0.8',
            'Mozilla/4.0 (compatible; MSIE 6.0; Windows XP 5.1)',
            'Mozilla/4.0 (compatible; MSIE 5.0; Windows 98; DigExt)',
            'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.8) Gecko/2009032609 Firefox/3.0.8',
            'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.8.1.6) Gecko/20070725 Firefox/2.0.0.6',
            'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)',
            'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30)',
            'Opera/9.20 (Windows NT 6.0; U; en)'
        );
        var $referer = '';
        var $curl_header = false;
        var $curl_follow = true;
        var $max_redirect = 4;
        var $temp_redirect; // count down from max_redirct when curl_follow is false
        //provy vars
       // var $proxy = false;

        var $login = '';

        function curl($proxy = false)
        {
            $hash = $this->hashcode();
            $this->cookie = "cache/cookies/$hash.txt";

            if($_SERVER['HTTP_USER_AGENT']) $this->useragent = $_SERVER['HTTP_USER_AGENT'];
            else $this->useragent = $this->useragents[rand(0,count($this->useragents))];

            //if($proxy) $this->getProxy();
        }

        // get a random proxy
       /* function getProxy()
        {
            global $proxy;
            $this->proxy = $proxy->getProxy();
        }*/

        function hashcode()
        {
            return md5($_SERVER['REQUEST_TIME'].rand(1, 9999));
        }

    function setcallback($func_name)
    {
        $this->callback = $func_name;
    }

        // If site requests basic Authentication
        function setLogin($user, $pass)
        {
            $this->login = true;
            $this->username = $user;
            $this->password = $pass;
            // login will be cleared after request
        }

    function dorequest($method, $url, $vars = array())
    {
            $this->temp_redirect = $this->max_redirect;
            $curl_inst = true;
            if ( !extension_loaded("curl") && !dl("curl.so") ) $curl_inst = false;

            if ($curl_inst) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                if($this->login) curl_setopt($ch, CURLOPT_USERPWD, $this->username.":".$this->password); // if login was sucessful, clear it (see below)
                curl_setopt($ch, CURLOPT_REFERER, $this->referer);            // Page url was found on

                curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
                if ($this->proxy) {
                    curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
                    //curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
                    //curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_ANY);
                }
                if (substr($url, 0, 5) == 'https') {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                }
                curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
                curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
                curl_setopt($ch, CURLOPT_AUTOREFERER, true);
                if ($this->curl_follow) {
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_MAXREDIRS, $this->max_redirect); // auto redirect
                }
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, $this->curl_header);        // Return Header info (true,false)
                $httpheader[] = "Connection: Keep-Alive";
                //$httpheader[] = "Accept-Encoding: compress, gzip"; // compressing inbound files -> gzuncompress($web_page['FILE']) to uncompress file
                //# Get sizes of compressed and uncompressed versions of web page Check LIB_download_image
                //$uncompressed_size   = strlen($web_page['FILE']);
                //$compressed_size     = strlen(gzcompress($web_page['FILE'], $compression_value = 9));
                //$noformat_size       = strlen(strip_tags($web_page['FILE']));
                curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                if ($method == "POST") {
                    $query = $this->makeQuery($vars);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
                }

                $return_array = $this->redirect_exec($ch);

                curl_close($ch);
                if($this->login) $this->login = false; // login was sucessful, clear it
                return $return_array;
            }

            $sockets_inst = true;
            if ( !extension_loaded("sockets") && !dl("sockets.so") ) $sockets_inst = false;

            if ($sockets_inst) {
                    $purl = parse_url($url);
                    $address = gethostbyname($purl['host']);
                    $socket = socket_create(AF_INET, SOCK_STREAM, 0);
                    if ($socket < 1) {
                            echo 'socket < 1\n';
                            echo socket_strerror($socket).'\n';
                            echo 'address: '.$address.'\n';
                            echo 'purl: '.$purl.'\n';
                            echo 'url: '.$url.'\n';
                            return false;
                    }
                    //try {
                        $res = socket_connect($socket, $address, 80);
                    //} catch {
                        //return false;
                    //}
                    if ($res < 1) {
                            echo 'res < 1\n';
                            echo socket_strerror($res).'\n';
                            echo 'address: '.$address.'\n';
                            echo 'purl: '.$purl.'\n';
                            echo 'url: '.$url.'\n';
                            return false;
                    }
                    if ($method == "GET") {
                            $request = "GET ".$purl['path']."?{$purl['query']} HTTP/1.0\r\n";
                            $request .= "Host: ".$purl['host']."\r\n";
                            $request .= "User-Agent: ".$_SERVER['HTTP_USER_AGENT']."\r\n";
                            $request .= "Connection: close\r\n\r\n";
                    } else if ($method == "POST") {
                            $query = "";
                            foreach ($vars as $k => $v) {
                                    $query .= $k."=".$v."&";
                            }
                            $query = substr($query, 0, -1);
                            $request = "POST ".$purl['path']." HTTP/1.0\r\n";
                            $request .= "Host: ".$purl['host']."\r\n";
                            $request .= "User-Agent: ".$_SERVER['HTTP_USER_AGENT']."\r\n";
                            $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
                            $request .= "Content-Length: ".strlen($query)."\r\n";
                            $request .= "Connection: close\r\n\r\n";
                            $request .= $query;
                    } else {
                            return false;
                    }
                    socket_write($socket, $request, strlen( $request) );
                    $data = "";
                    while ($resp = socket_read($socket, 2048)) {
                            $data .= $resp;
                    }
                    if (preg_match("/HTTP\\/.\\.. 30./", $data)) {
                            preg_match("/Location: (.*)/", $data, $matches);
                            $purlm = "http://".$purl['host']."{$matches['1']}";
                            $purlm = rtrim($purlm);
                            $data = $this->dorequest($method, $purlm, $vars);
                    }
                    $pos = strpos($data, "\r\n\r\n");
                    if ($pos != false) {
                            $data = substr($data, $pos + 4);
                    }
                    return $data;
            }
            return false;
    }

        function redirect_exec($ch)
        {
            $return_array['FILE']   = curl_exec($ch);
            $return_array['STATUS'] = curl_getinfo($ch);
            $return_array['ERROR']  = curl_error($ch);
            if($this->curl_follow || !$this->curl_header) return $return_array; // internal curl took care of it

            // Full redirect
            // $curl_follow = false
            // $curl_header = true
            $data = $return_array['FILE'];
            $http_code = $return_array['STATUS']['http_code'];
            if ($http_code == 301 || $http_code == 302 || $http_code == 303) {
                list($header) = explode("\r\n\r\n", $data, 2);
                $matches = array();
                preg_match('/(Location:|URI:)(.*?)\n/', $header, $matches);
                $url = trim(array_pop($matches));
                $url_parsed = parse_url($url);
                if (isset($url_parsed['host'])) {
                        curl_setopt($ch, CURLOPT_URL, $url);
                        $this->temp_redirect--;
                        return $this->redirect_exec($ch);
                }
            } elseif ($http_code == 200) {
                    $matches = array();
                    preg_match('/(<meta http-equiv=)(.*?)(refresh)(.*?)(url=)(.*?)[\'|"]\s*>/', strtolower($data), $matches);
                    $url = trim(array_pop($matches));
                    $url_parsed = parse_url($url);
                    if (isset($url_parsed['host'])) {
                        curl_setopt($ch, CURLOPT_URL, $url);
                        $this->temp_redirect--;
                        return $this->redirect_exec($ch);
                    }
            } else if ($return_array['FILE'] === false || $http_code != 200) {
                // FAILED
            }
            return $return_array;
        }

    function get($url, $vars = array())
    {
                $query = $this->makeQuery($vars);
                if($query && !strpos($url, "?")) $query = "?".$query;
                else if($query && strpos($url, "?")) $query = "&".$query;
        return $this->dorequest("GET", $url.$query, "null");
    }

    function post($url, $vars = array())
    {
        return $this->dorequest("POST", $url, $vars);
    }

        function clearCookie()
        {

            if (file_exists($this->cookie)) unlink($this->cookie);
        }

        function makeQuery($vars)
        {
            $query = "";
            foreach ($vars as $k => $v) {
                $query .= $k."=".urlencode($v)."&";
            }
            $query = substr($query, 0, -1); // remove trailing &
            return $query;
        }

}

?>
