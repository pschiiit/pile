<?php

class PileStdHandler
{
  public $app;
  
  /**
   * Returns allowed request methods
   *
   * @return array
   */
  public static function request_methods()
  {
    return array("GET","POST","PUT","DELETE", "HEAD");
  }
  
  /**
   * Returns current request method for a given environment or current one
   *
   * @param string $env 
   * @return string
   */
  public static function request_method($env = null)
  {
    if(is_null($env)) $env = self::env();
    $m = array_key_exists('REQUEST_METHOD', $env['SERVER']) ? $env['SERVER']['REQUEST_METHOD'] : null;
    if($m == "POST" && array_key_exists('_method', $env['POST'])) 
      $m = strtoupper($env['POST']['_method']);
    if(!in_array(strtoupper($m), self::request_methods()))
    {
      trigger_error("'$m' request method is unkown or unavailable.", E_USER_WARNING);
      $m = false;
    }
    return $m;
  }
  
  public static function env()
  {
    static $env = array();

    if(empty($env))
    {
      if(empty($GLOBALS['_SERVER']))
      {
        // Fixing empty $GLOBALS['_SERVER'] bug 
        // http://sofadesign.lighthouseapp.com/projects/29612-limonade/tickets/29-env-is-empty
        $GLOBALS['_SERVER']  =& $_SERVER;
        $GLOBALS['_FILES']   =& $_FILES;
        $GLOBALS['_REQUEST'] =& $_REQUEST;
        $GLOBALS['_SESSION'] =& $_SESSION;
        $GLOBALS['_ENV']     =& $_ENV;
        $GLOBALS['_COOKIE']  =& $_COOKIE;
      }

      $glo_names = array('SERVER', 'FILES', 'REQUEST', 'SESSION', 'ENV', 'COOKIE');

      $vars = array_merge($glo_names, self::request_methods());
      foreach($vars as $var)
      {
        $varname = "_$var";
        if(!array_key_exists($varname, $GLOBALS)) $GLOBALS[$varname] = array();
        $env[$var] =& $GLOBALS[$varname];
      }

      $method = self::request_method($env);
      if($method == 'PUT' || $method == 'DELETE')
      {
        $varname = "_$method";
        if(array_key_exists('_method', $_POST) && $_POST['_method'] == $method)
        {
          foreach($_POST as $k => $v)
          {
            if($k == "_method") continue;
            $GLOBALS[$varname][$k] = $v;
          }
        }
        else
        {
          parse_str(file_get_contents('php://input'), $GLOBALS[$varname]);
        }
      }
    }
    return $env;
  }
  
  /**
   * Returns HTTP response status for a given code.
   * If no code provided, return an array of all status
   *
   * @param string $num 
   * @return string,array
   */
  public function http_response_status($num = null)
  {
    $status =  array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',

        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        226 => 'IM Used',

        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',

        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',

        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        510 => 'Not Extended'
    );
    if(is_null($num)) return $status;
    return array_key_exists($num, $status) ? $status[$num] : '';
  }
  
  /**
   * Returns an HTTP response status string for a given code
   *
   * @param string $num 
   * @return string
   */
  public function http_response_status_code($num)
  {
    if($str = self::http_response_status($num)) return "HTTP/1.1 $num $str";
  }
  
  public function run($app, $options = array())
  {
    foreach($options as $k => $v) ini_set($k, $v);
    $this->serve($app);
  }
  
  public function serve($app)
  {
    $env = self::env();
    ob_start();
    list($status, $headers, $body) = $app->call($env);

    if($output = ob_get_clean()) 
    {
      # unexpected output
      $headers["X-Output"] = json_encode($output);
    }
    $this->send_headers($status, $headers);
    $this->send_body($body);
  }

  
  public function send_headers($status, $headers)
  {
    $str = self::http_response_status_code($status);
    header($str);
    foreach($headers as $k=>$v) header("$k: $v");
  }
  
  # ! penser à utiliser plutôt le output buffer pour stocker le body ?
  # plus speed notamment dans le cas d'output de fichiers ?
  public function send_body($body = array())
  {
    foreach($body as $part) echo $part, "\n";
  }
}

class PileStandardPHPHandler
{
  public $app;
  
  /**
   * Returns allowed request methods
   *
   * @return array
   */
  public static function request_methods()
  {
    return array("GET","POST","PUT","DELETE", "HEAD");
  }
  
  /**
   * Returns current request method for a given environment or current one
   *
   * @param string $env 
   * @return string
   */
  public static function request_method($env = null)
  {
    if(is_null($env)) $env = self::env();
    $m = array_key_exists('REQUEST_METHOD', $env['SERVER']) ? $env['SERVER']['REQUEST_METHOD'] : null;
    if($m == "POST" && array_key_exists('_method', $env['POST'])) 
      $m = strtoupper($env['POST']['_method']);
    if(!in_array(strtoupper($m), self::request_methods()))
    {
      trigger_error("'$m' request method is unkown or unavailable.", E_USER_WARNING);
      $m = false;
    }
    return $m;
  }
  
  public static function env()
  {
    static $env = array();

    if(empty($env))
    {
      if(empty($GLOBALS['_SERVER']))
      {
        // Fixing empty $GLOBALS['_SERVER'] bug 
        // http://sofadesign.lighthouseapp.com/projects/29612-limonade/tickets/29-env-is-empty
        $GLOBALS['_SERVER']  =& $_SERVER;
        $GLOBALS['_FILES']   =& $_FILES;
        $GLOBALS['_REQUEST'] =& $_REQUEST;
        $GLOBALS['_SESSION'] =& $_SESSION;
        $GLOBALS['_ENV']     =& $_ENV;
        $GLOBALS['_COOKIE']  =& $_COOKIE;
      }

      $glo_names = array('SERVER', 'FILES', 'REQUEST', 'SESSION', 'ENV', 'COOKIE');

      $vars = array_merge($glo_names, self::request_methods());
      foreach($vars as $var)
      {
        $varname = "_$var";
        if(!array_key_exists($varname, $GLOBALS)) $GLOBALS[$varname] = array();
        $env[$var] =& $GLOBALS[$varname];
      }

      $method = self::request_method($env);
      if($method == 'PUT' || $method == 'DELETE')
      {
        $varname = "_$method";
        if(array_key_exists('_method', $_POST) && $_POST['_method'] == $method)
        {
          foreach($_POST as $k => $v)
          {
            if($k == "_method") continue;
            $GLOBALS[$varname][$k] = $v;
          }
        }
        else
        {
          parse_str(file_get_contents('php://input'), $GLOBALS[$varname]);
        }
      }
    }
    return $env;
  }
  
  /**
   * Returns HTTP response status for a given code.
   * If no code provided, return an array of all status
   *
   * @param string $num 
   * @return string,array
   */
  public function http_response_status($num = null)
  {
    $status =  array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',

        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        226 => 'IM Used',

        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',

        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',

        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        510 => 'Not Extended'
    );
    if(is_null($num)) return $status;
    return array_key_exists($num, $status) ? $status[$num] : '';
  }
  
  /**
   * Returns an HTTP response status string for a given code
   *
   * @param string $num 
   * @return string
   */
  public function http_response_status_code($num)
  {
    if($str = self::http_response_status($num)) return "HTTP/1.1 $num $str";
  }
  
  public function run($app, $options = array())
  {
    foreach($options as $k => $v) ini_set($k, $v);
    $this->serve($app);
  }
  
  public function serve($app)
  {
    $env = self::env();
    ob_start();
    list($status, $headers, $body) = $app->call($env);

    if($output = ob_get_clean()) 
    {
      # unexpected output
      $headers["X-Output"] = json_encode($output);
    }
    $this->send_headers($status, $headers);
    $this->send_body($body);
  }

  
  public function send_headers($status, $headers)
  {
    $str = self::http_response_status_code($status);
    header($str);
    foreach($headers as $k=>$v) header("$k: $v");
  }
  
  # ! penser à utiliser plutôt le output buffer pour stocker le body ?
  # plus speed notamment dans le cas d'output de fichiers ?
  public function send_body($body = array())
  {
    foreach($body as $part) echo $part, "\n";
  }
}

