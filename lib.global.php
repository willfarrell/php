<?php

/** GLOBAL **/
//include('htmLawed.php');

// hashing
function bcrypt_hash($password, $work_factor = 8)
{
    if (version_compare(PHP_VERSION, '5.3') < 0) throw new Exception('Bcrypt requires PHP 5.3 or above');

    if (! function_exists('openssl_random_pseudo_bytes')) {
        throw new Exception('Bcrypt requires openssl PHP extension');
    }

    if ($work_factor < 4 || $work_factor > 31) $work_factor = 8;
    $salt = 
        '$2a$' . str_pad($work_factor, 2, '0', STR_PAD_LEFT) . '$' .
        substr(
            strtr(base64_encode(openssl_random_pseudo_bytes(16)), '+', '.'), 
            0, 22
        )
    ;
	//echo "$password, $salt \n";
    return crypt($password, $salt);
}

function bcrypt_check($password, $stored_hash, $legacy_handler = NULL)
{
    if (version_compare(PHP_VERSION, '5.3') < 0) throw new Exception('Bcrypt requires PHP 5.3 or above');

    if (bcrypt_is_legacy_hash($stored_hash)) {
        if ($legacy_handler) return call_user_func($legacy_handler, $password, $stored_hash);
        else throw new Exception('Unsupported hash format');
    }

    return crypt($password, $stored_hash) == $stored_hash;
}

function bcrypt_is_legacy_hash($hash) { return substr($hash, 0, 4) != '$2a$'; }

// other

function br2nl($text) {
    return  preg_replace('/<br\\s*?\/??>/i', '', $text);
}

function set_cookie_fix_domain($Name, $Value = '', $Expires = 0, $Path = '', $Domain = '', $Secure = false, $HTTPOnly = false) {
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

function preg_replace_all($pattern,$replace,$text) {
    while(preg_match($pattern,$text))
        $text = preg_replace($pattern,$replace,$text);
    return $text;
}

function getURL($uri = false) {
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

function redirectToHTTPS($on = true) {
  	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=="on" && $on) {
     	$redirect= "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
  	} else if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=="on" && !$on) {
        $redirect= "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    } else {
		return;
	}
	header("Location:$redirect");
}

function url_exists($url) {
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


/** 
 * Convert an xml file or string to an associative array (including the tag attributes): 
 * $domObj = new xmlToArrayParser($xml); 
 * $elemVal = $domObj->array['element'] 
 * Or:  $domArr=$domObj->array;  $elemVal = $domArr['element']. 
 * 
 * @version  2.0 
 * @param Str $xml file/string. 
 */ 
class xmlToArrayParser { 
  /** The array created by the parser can be assigned to any variable: $anyVarArr = $domObj->array.*/ 
  public  $array = array(); 
  public  $parse_error = false; 
  private $parser; 
  private $pointer; 
  
  /** Constructor: $domObj = new xmlToArrayParser($xml); */ 
  public function __construct($xml) { 
    $this->pointer =& $this->array; 
    $this->parser = xml_parser_create("UTF-8"); 
    xml_set_object($this->parser, $this); 
    xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false); 
    xml_set_element_handler($this->parser, "tag_open", "tag_close"); 
    xml_set_character_data_handler($this->parser, "cdata"); 
    $this->parse_error = xml_parse($this->parser, ltrim($xml))? false : true; 
  } 
  
  /** Free the parser. */ 
  public function __destruct() { xml_parser_free($this->parser);} 

  /** Get the xml error if an an error in the xml file occured during parsing. */ 
  public function get_xml_error() { 
    if($this->parse_error) { 
      $errCode = xml_get_error_code ($this->parser); 
      $thisError =  "Error Code [". $errCode ."] \"<strong style='color:red;'>" . xml_error_string($errCode)."</strong>\", 
                            at char ".xml_get_current_column_number($this->parser) . " 
                            on line ".xml_get_current_line_number($this->parser).""; 
    }else $thisError = $this->parse_error; 
    return $thisError; 
  } 
  
  private function tag_open($parser, $tag, $attributes) { 
    $this->convert_to_array($tag, 'attrib'); 
    $idx=$this->convert_to_array($tag, 'cdata'); 
    if(isset($idx)) { 
      $this->pointer[$tag][$idx] = Array('@idx' => $idx,'@parent' => &$this->pointer); 
      $this->pointer =& $this->pointer[$tag][$idx]; 
    }else { 
      $this->pointer[$tag] = Array('@parent' => &$this->pointer); 
      $this->pointer =& $this->pointer[$tag]; 
    } 
    if (!empty($attributes)) { $this->pointer['attrib'] = $attributes; } 
  } 

  /** Adds the current elements content to the current pointer[cdata] array. */ 
  private function cdata($parser, $cdata) { $this->pointer['cdata'] = trim($cdata); } 

  private function tag_close($parser, $tag) { 
    $current = & $this->pointer; 
    if(isset($this->pointer['@idx'])) {unset($current['@idx']);} 
    
    $this->pointer = & $this->pointer['@parent']; 
    unset($current['@parent']); 
    
    if(isset($current['cdata']) && count($current) == 1) { $current = $current['cdata'];} 
    else if(empty($current['cdata'])) {unset($current['cdata']);} 
  } 
  
  /** Converts a single element item into array(element[0]) if a second element of the same name is encountered. */ 
  private function convert_to_array($tag, $item) { 
    if(isset($this->pointer[$tag][$item])) { 
      $content = $this->pointer[$tag]; 
      $this->pointer[$tag] = array((0) => $content); 
      $idx = 1; 
    }else if (isset($this->pointer[$tag])) { 
      $idx = count($this->pointer[$tag]); 
      if(!isset($this->pointer[$tag][0])) { 
        foreach ($this->pointer[$tag] as $key => $value) { 
            unset($this->pointer[$tag][$key]); 
            $this->pointer[$tag][0][$key] = $value; 
    }}}else $idx = null; 
    return $idx; 
  } 
} 



?>