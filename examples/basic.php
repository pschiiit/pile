<?php

/**
* 
*/
class BasicApp
{
  
  function __construct()
  {
    # code...
  }
  
  public function call($env)
  {
    $body = array();
    $body[] = "<h1>My Basic Application</h1>"
            . "<pre>"
            . print_r($env, true)  
            . "</pre>";
    $headers = array();
    $headers['my_app_header'] = "basic";
    $status = 200;
    $response = array($status, $headers, $body);
    return $response;
  }
}

require_once '../lib/phpile.php';

$app = new PhpileBuilder();
$app->use_middleware('PhpileCommonLogger', new PhpileFileLogger('basic.log'));
// $app->use_middleware('PhpileShowExceptions');
// $app->use_middleware('PhpileStatic', array( 'urls' => array("/css", "/images"), 
//                                'root' => 'public' ));
$app->run(new BasicApp());

$server = new PhpileStandardPHPHandler();
$server->run($app, array( 'display_errors'   => 1, 
                          'register_globals' => 0   ));
