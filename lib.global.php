<?php

/** GLOBAL **/
//include('htmLawed.php');

function br2nl($text)
{
    return  preg_replace('/<br\\s*?\/??>/i', '', $text);
}

function set_cookie_fix_domain($Name, $Value = '', $Expires = 0, $Path = '', $Domain = '', $Secure = false, $HTTPOnly = false)
{
    if (!empty($Domain)) {
        // Fix the domain to accept domains with and without 'www.'.
        if (strtolower(substr($Domain, 0, 4)) == 'www.')  $Domain = substr($Domain, 4);
        $Domain = '.' . $Domain;

        // Remove port information.
        $Port = strpos($Domain, ':');
        if ($Port !== false)  $Domain = substr($Domain, 0, $Port);
    }

    header('Set-Cookie: ' . rawurlencode($Name) . '=' . rawurlencode($Value)
                                                . (empty($Expires) ? '' : '; expires=' . gmdate('D, d-M-Y H:i:s', $Expires) . ' GMT')
                                                . (empty($Path) ? '' : '; path=' . $Path)
                                                . (empty($Domain) ? '' : '; domain=' . $Domain)
                                                . (!$Secure ? '' : '; secure')
                                                . (!$HTTPOnly ? '' : '; HttpOnly'), false);
}

function preg_replace_all($pattern,$replace,$text)
{
    while(preg_match($pattern,$text))
        $text = preg_replace($pattern,$replace,$text);
    return $text;
}

function getURL($uri = false)
{
    $pageURL = 'http';
    if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") $pageURL .= "s";
    $pageURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
        if($uri) $pageURL .= $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"];
        if($uri) $pageURL .= $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

function redirectToHTTPS($on = true)
{
  	if ($_SERVER['HTTPS']!=="on" && $on) {
     	$redirect= "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
  	} else if ($_SERVER['HTTPS']=="on" && !$on) {
        $redirect= "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    } else {
		return;
	}
	header("Location:$redirect");
}

function url_exists($url)
{
    // Version 4.x supported
    $handle   = curl_init($url);
    if (false === $handle) {
        return false;
    }
    curl_setopt($handle, CURLOPT_HEADER, false);
    curl_setopt($handle, CURLOPT_FAILONERROR, true);  // this works
    curl_setopt($handle, CURLOPT_HTTPHEADER, Array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15") ); // request as if Firefox
    curl_setopt($handle, CURLOPT_NOBODY, true);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);
    $connectable = curl_exec($handle);
    curl_close($handle);
    return $connectable;
}

function echoFile($folder, $file)
{
    //header("Content-Type: " . mime_content_type($FileName));
    // if you are not allowed to use mime_content_type, then hardcode MIME type
    // use application/octet-stream for any binary file
    // use application/x-executable-file for executables
    // use application/x-zip-compressed for zip files
    header("Content-Type: application/octet-stream");
    header("Content-Length: " . filesize($folder.$file));
    header("Content-Disposition: attachment; filename=\"$file\"");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    $fp = fopen($folder.$file,"rb");
    fpassthru($fp);
    fclose($fp);
}


/********************************
 * Retro-support of get_called_class()
 * Tested and works in PHP 5.2.4
 * http://www.sol1.com.au/
 ********************************/
if (!function_exists('get_called_class')) {
function get_called_class($bt = false, $l = 1)
{
    if (!$bt) $bt = debug_backtrace();
    if (!isset($bt[$l])) throw new Exception("Cannot find called class -> stack level too deep.");
    if (!isset($bt[$l]['type'])) {
        throw new Exception ('type not set');
    }
    else switch ($bt[$l]['type']) {
        case '::':
            $lines = file($bt[$l]['file']);
            $i = 0;
            $callerLine = '';
            do {
                $i++;
                $callerLine = $lines[$bt[$l]['line']-$i] . $callerLine;
            } while (stripos($callerLine,$bt[$l]['function']) === false);
            preg_match('/([a-zA-Z0-9\_]+)::'.$bt[$l]['function'].'/',
                        $callerLine,
                        $matches);
            if (!isset($matches[1])) {
                // must be an edge case.
                throw new Exception ("Could not find caller class: originating method call is obscured.");
            }
            switch ($matches[1]) {
                case 'self':
                case 'parent':
                    return get_called_class($bt,$l+1);
                default:
                    return $matches[1];
            }
            // won't get here.
        case '->': switch ($bt[$l]['function']) {
                case '__get':
                    // edge case -> get class of calling object
                    if (!is_object($bt[$l]['object'])) throw new Exception ("Edge case fail. __get called on non object.");
                    return get_class($bt[$l]['object']);
                default: return $bt[$l]['class'];
            }

        default: throw new Exception ("Unknown backtrace method type");
    }
}
}






?>