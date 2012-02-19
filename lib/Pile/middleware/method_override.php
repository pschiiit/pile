<?php

function pile_middleware_method_override($env)
{
  if (!empty($env["REQUEST_METHOD"]) AND $env["REQUEST_METHOD"] == "POST") {
    
    $method = !empty($_POST['_method'])
      ? $_POST['_method']
      : (!empty($env['HTTP_X_HTTP_METHOD_OVERRIDE']) ? $env['HTTP_X_HTTP_METHOD_OVERRIDE'] : null);
    $method = strtoupper($method);
    
    if (in_array($method, array('GET', 'HEAD', 'PUT', 'POST', 'DELETE', 'OPTIONS'))) {
      $env["REQUEST_METHOD"] = $method;
    }
  }
  return Pile_Builder::call($env);
}
