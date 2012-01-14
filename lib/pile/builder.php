<?php

class PileBuilder
{
  private $_app;
  private $_apps_stack;
  
  public function __construct()
  {
    $this->_apps_stack = array();
  }
  
  public function call(array $env)
  {
    # app = MyRackApp.new 
    # app = Rack::Lint.new(app) 
    # app = Rack::ShowStatus.new(app) 
    # app = Rack::ShowExceptions.new(app) 
    # app = Rack::CommonLogger.new(app)
    #
    # Is the same as
    # 
    # app = Rack::Builder.new do 
    #   use Rack::CommonLogger 
    #   use Rack::ShowExceptions 
    #   use Rack::ShowStatus 
    #   use Rack::Lint 
    #   run MyRackApp.new 
    # end
    
    # Note that the use-statements are written in “reverse” order, 
    # the outermost ﬁrst.
    $app   = $this->_app;
    $stack = $this->_apps_stack;
    while($middleware = array_pop($stack))
    {
      // var_dump($middleware);
      $class_name     = $middleware[0];
      $args           = array_merge(array($app), $middleware[1]);

      $class = new ReflectionClass($class_name);
      # http://www.php.net/manual/en/reflectionclass.newinstanceargs.php
      # > Annoyingly, this will throw an exception for classes with no constructor even if you pass an empty array for the arguments. For generic programming you should avoid this function and use call_user_func_array with newInstance.
      # so prefer
      $app = call_user_func_array(array($class, 'newInstance'), $args);
      # which is available for (PHP5)
      # to 
      # $app = $class->newInstanceArgs($args); // (PHP 5 >= 5.1.3)
      
      
      # TODO add case when $middleware is a function, not a class
    }
    return $app->call($env);
  }
  
  public function run($app)
  {
    $this->_app = $app;
  }
  
  # use is an internal keywork… looking for a more appropriate
  # function name than "use_middleware" to register middleware ?
  # use_app ?
  public function use_middleware($middleware_class)
  {
    $args = func_get_args();
    $middleware_class = array_shift($args);
    $this->_apps_stack[] = array($middleware_class, $args);
  }
}

