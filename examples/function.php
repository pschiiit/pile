<?php

function single_function_app($env)
{
  return array(
    200,
    array(),
    "<!DOCTYPE html>
    <html>
      <head><title>Pile - Function example</title></head>
      <body><p>Generated on " . date('Y-m-d') ." at " . date('H:i') ."</p></body>
    </html>"
    );
}

require_once '../lib/pile.php';

$pile = new Pile_Builder();
$pile->middlewares()->prepend('pile_middleware_runtime', 'runtime');
$pile->app = 'single_function_app';

$server = new Pile_StdHandler();
$server->serve($pile);
