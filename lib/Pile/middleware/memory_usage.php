<?php

function pile_middleware_memory_usage($env)
{
  $start = memory_get_usage();
  
  list($status, $headers, $body) = Pile_Builder::call($env);
  
  $headers['X-Memory-Global'] = memory_get_usage();
  $headers['X-Memory-Self']   = (memory_get_usage() - $start);
  $headers['X-Memory-Peak']   = memory_get_peak_usage();
  
  return array($status, $headers, $body);
}
