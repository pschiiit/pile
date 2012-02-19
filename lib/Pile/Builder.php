<?php

class Pile_Builder
{
  protected static $_instances = array();
  
  public $app = null;
  public $uid = null;
  
  protected $_middlewares = array();
  
  public function __construct($use_default_middlewares_stack = true)
  {
    $this->_middlewares = new Pile_Stack($use_default_middlewares_stack);
    
    $this->uid = uniqid('pile_builder', true);
    self::$_instances[$this->uid] = $this;
  }
  
  public function middlewares()
  {
      return $this->_middlewares;
  }
  
  public static function call($env)
  {
    $instance = self::$_instances[$env['pile.uid']];
    return $instance->next($env);
  }
  
  public function next($env)
  {
    if (false === $callable = $this->middlewares()->next()) {
      throw new Exception('Already at endpoint');
    }
    return call_user_func($callable, $env);
  }
  
  public function run($env)
  {
    if (null === $this->app) {
      throw new InvalidArgumentException('No app defined');
    }
    $this->middlewares()->append($this->app);
    
    $callable = $this->middlewares()->rewind();
    return call_user_func($callable, $env);
  }
}