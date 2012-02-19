<?php

function pile_middleware_runtime($env)
{
  $start = microtime(true);
  list($status, $headers, $body) = Pile_Builder::call($env);
  $headers['X-Runtime'] = sprintf('%0.3f', (microtime(true) - $start));
  return array($status, $headers, $body);
}
