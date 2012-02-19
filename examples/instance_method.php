<?php

class Application
{
  protected $name = null;
    
  public function __construct($name = 'World')
  {
    $this->name = $name;
  }
    
  public function call($env)
  {
    return array(
      200,
      array(),
      "<!DOCTYPE html>
      <html>
        <head><title>Pile - Instance method application</title></head>
        <body><h1>Hello " . $this->name . "</h1><p>Generated on " . date('Y-m-d') ." at " . date('H:i') ."</p></body>
      </html>"
      );
  }
  
  public static function run($env)
  {
    return $this->call($env);
  }
}

require_once '../lib/pile.php';

$app = new Application('from Pile  !');
$pile = new Pile_Builder();

/*
 * As Application.call() and Application.run() return the same,
 * these three are equivalent :
 *   - $pile->app = $app;
 *   - $pile->app = array($app, 'call');
 *   - $pile->app = array($app, 'run'); 
 */
$pile->app = $app;

$server = new Pile_StdHandler();
$server->serve($pile);
