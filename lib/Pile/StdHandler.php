<?php

class Pile_StdHandler
{
  protected $app = null;
  
  protected static $_env = null;
  
  public static function env()
  {
    if(null == self::$_env) {
      
      self::$_env = array();
      
      if(empty($GLOBALS['_SERVER'])) {
        $GLOBALS['_SERVER']  =& $_SERVER;
        $GLOBALS['_FILES']   =& $_FILES;
        $GLOBALS['_REQUEST'] =& $_REQUEST;
        $GLOBALS['_SESSION'] =& $_SESSION;
        $GLOBALS['_ENV']     =& $_ENV;
        $GLOBALS['_COOKIE']  =& $_COOKIE;
      }

      $glo_names = array('SERVER', 'FILES', 'REQUEST', 'SESSION', 'ENV', 'COOKIE');

      $vars = array_merge($glo_names, Pile_Http::request_methods());
      foreach($vars as $var) {
        $varname = "_$var";
        if(!array_key_exists($varname, $GLOBALS)) $GLOBALS[$varname] = array();
        self::$_env[$var] =& $GLOBALS[$varname];
      }

      /*
      $method = self::request_method($env);
      if($method == 'PUT' || $method == 'DELETE') {
        $varname = "_$method";
        if(array_key_exists('_method', $_POST) && $_POST['_method'] == $method) {
          foreach($_POST as $k => $v) {
            if($k == "_method") continue;
            $GLOBALS[$varname][$k] = $v;
          }
        } else {
          parse_str(file_get_contents('php://input'), $GLOBALS[$varname]);
        }
      }
      */
    }
    
    return self::$_env;
  }
  
  public function serve($app, $options = array())
  {
    foreach($options as $k => $v) ini_set($k, $v);

    $env = self::env();
    $env['pile.uid'] = $app->uid;
    
    //ob_start();
    list($status, $headers, $body) = $app->run($env);
    /*if($output = ob_get_clean()) {
      # unexpected output
      $headers["X-Output"] = json_encode($output);
    }*/
    
    $this->_headers($status, $headers);
    $this->_body($body);
  }
  
  
  protected function _status($num)
  {
    if($str = Pile_Http::response_status($num)) return "HTTP/1.1 $num $str";
  }
  
  protected function _headers($status, $headers = array())
  {
    header($this->_status($status));
    foreach($headers as $k => $v) {
      header("$k: $v");
    }
  }
  
  # ! penser à utiliser plutôt le output buffer pour stocker le body ?
  # plus speed notamment dans le cas d'output de fichiers ?
  protected function _body($body = array())
  {
    if (!is_array($body)) {
      echo $body;
    }
    foreach($body as $part) {
      echo $part, "\n";
    }
  }
}

