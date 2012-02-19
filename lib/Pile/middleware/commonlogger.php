<?php

class PileCommonLogger
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

