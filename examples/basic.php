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

require_once '../lib/pile.php';

$app = new PileBuilder();
$app->use_middleware('PileCommonLogger', new PileFileLogger('basic.log'));
// $app->use_middleware('PileShowExceptions');
// $app->use_middleware('PileStatic', array( 'urls' => array("/css", "/images"), 
//                                'root' => 'public' ));
$app->run(new BasicApp());

$server = new PileStdHandler();
$server->run($app, array( 'display_errors'   => 1, 
                          'register_globals' => 1   ));
