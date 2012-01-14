<?php

# <http://xhtml.net/php/pluf-framework-php/600-Combiner-type-hinting-et-interfaces-en-PHP-pour-securiser-son-code>
# "Le type hinting et les interfaces ont un impact au niveau des performances et
# complexifie le code, donc ces outils doivent être utilisés avec soin"
# "Un endroit où je n’utiliserais pas le type hinting et les interfaces est par
# exemple la boucle de dispatch dans une application web."
# 
# TODO: virer les interfaces et type hinting pour la version finale

// interface PhpileIApp
// {
//   public function __construct(self $app);
//   public function call(array $env);
// }

# Cf rack.php, qui est assez intéressant
class PhpileStandardPHPHandler
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

# limonade adapter
#
# ! pas sûr que ce soit utile
class PhpileLimonadeAdapter
{
  private $app;
  
  public function __construct($app)
  {
    $this->app = $app;
  }
  
  public function call(array $env)
  {
    # code...
  }
}

# Builder
class PhpileBuilder
{
  private $_app;
  private $_apps_stack;
  
  public function __construct()
  {
    $this->_apps_stack = array();
  }
  
  public function call(array $env)
  {
    # app = MyRackApp.new 
    # app = Rack::Lint.new(app) 
    # app = Rack::ShowStatus.new(app) 
    # app = Rack::ShowExceptions.new(app) 
    # app = Rack::CommonLogger.new(app)
    #
    # Is the same as
    # 
    # app = Rack::Builder.new do 
    #   use Rack::CommonLogger 
    #   use Rack::ShowExceptions 
    #   use Rack::ShowStatus 
    #   use Rack::Lint 
    #   run MyRackApp.new 
    # end
    
    # Note that the use-statements are written in “reverse” order, 
    # the outermost ﬁrst.
    $app   = $this->_app;
    $stack = $this->_apps_stack;
    while($middleware = array_pop($stack))
    {
      // var_dump($middleware);
      $class_name     = $middleware[0];
      $args           = array_merge(array($app), $middleware[1]);

      $class = new ReflectionClass($class_name);
      # http://www.php.net/manual/en/reflectionclass.newinstanceargs.php
      # > Annoyingly, this will throw an exception for classes with no constructor even if you pass an empty array for the arguments. For generic programming you should avoid this function and use call_user_func_array with newInstance.
      # so prefer
      $app = call_user_func_array(array($class, 'newInstance'), $args);
      # which is available for (PHP5)
      # to 
      # $app = $class->newInstanceArgs($args); // (PHP 5 >= 5.1.3)
      
      
      # TODO add case when $middleware is a function, not a class
    }
    return $app->call($env);
  }
  
  public function run($app)
  {
    $this->_app = $app;
  }
  
  # use is an internal keywork… looking for a more appropriate
  # function name than "use_middleware" to register middleware ?
  # use_app ?
  public function use_middleware($middleware_class)
  {
    $args = func_get_args();
    $middleware_class = array_shift($args);
    $this->_apps_stack[] = array($middleware_class, $args);
  }
}

class PhpileCommonLogger
{
  public function __construct($app, $logger=null)
  {
    $this->app = $app;
    $this->logger = $logger;
  }
  public function call($env)
  {
    $began_at = time();
    list($status, $header, $body) = $this->app->call($env);
    $this->log($env, $status, $header, $began_at);
    return array($status, $header, $body);
  }
  public function log($env, $status, $header, $began_at)
  {
    
    if(is_null($this->logger))
    {
      # TODO  default logger implementation
    }
    
    $str = date(DATE_ATOM)
         . " "
         . $env['SERVER']["REQUEST_METHOD"]
         . " "
         . $env['SERVER']["REQUEST_URI"]
         . (empty($env['SERVER']["QUERY_STRING"]) ? "" : "?"+$env['SERVER']["QUERY_STRING"]);
         
    $this->logger->write($str);
  }
}

class PhpileFileLogger
{
  public function __construct($file_path)
  {
    $this->path = $file_path;
  }
  
  public function write($str)
  {
    file_put_contents($this->path, $str . "\n", FILE_APPEND);
  }
}

class PhpileShowExceptions
{
  
}


