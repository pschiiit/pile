<?php

class StaticApplication
{
  public static function call($env)
  {
    return array(
      200,
      array(),
      "<!DOCTYPE html>
      <html>
        <head><title>Pile - Static method example</title></head>
        <body><p>Generated on " . date('Y-m-d') ." at " . date('H:i') ."</p></body>
      </html>"
      );
  }
  
  public static function run($env)
  {
    return self::call($env);
  }
}

require_once '../lib/pile.php';

$pile = new Pile_Builder();

/*
 * As StaticApplication::call() and StaticApplication::run() return the same,
 * these three are equivalent :
 *   - $pile->app = 'StaticApplication';
 *   - $pile->app = array('StaticApplication', 'call');
 *   - $pile->app = array('StaticApplication', 'run'); 
 */
$pile->app = 'StaticApplication';

$server = new Pile_StdHandler();
$server->serve($pile);
