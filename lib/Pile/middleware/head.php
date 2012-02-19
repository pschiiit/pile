<?php

function pile_middleware_head($env)
{
  list($status, $headers, $body) = Pile_Builder::call($env);

  if (!empty($env["REQUEST_METHOD"]) AND $env['REQUEST_METHOD'] == "HEAD") {
    $body = array();
  }
  return array($status, $headers, $body);
}
