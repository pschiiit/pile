<?php

class Pile_Stack implements Iterator, ArrayAccess
{
  const AFTER  = 'after';
  const BEFORE = 'before';
    
  protected $_stack = array();

  public function __construct($use_default_middlewares_stack = true)
  {
    if ($use_default_middlewares_stack) {
      $this->reset();
    }
  }
  
  public function reset()
  {
    $this->_stack = array(
      # TODO: uncomment when middlewares are ready
      # 'head'            => 'pile_middleware_head',
      # 'method_override' => 'pile_middleware_method_override',
      );
  }
  
  
  /*
   * Stack manipulations
   */
  
  
  public function insert_before($offset, $middleware, $name = null)
  {
    return $this->_insert(Pile_Stack::BEFORE, $offset, $middleware, $name);
  }
  
  public function insert_after($offset, $middleware, $name = null)
  {
    return $this->_insert(Pile_Stack::AFTER, $offset, $middleware, $name);
  }
  
  
  public function replace($name, $middleware)
  {
    $this->_stack[$name] = $middleware;
  }
  
  
  public function push($middleware, $name = null)
  {
    list($name, $callable) = $this->_normalize_arguments($middleware, $name);
    $this->_stack[$name] = $callable;
  }
  
  public function append($middleware, $name = null)
  {
    return $this->push($middleware, $name);
  }
  
  public function pop()
  {
    return array_pop($this->_stack);
  }
  
  
  public function unshift($middleware, $name = null)
  {
    list($name, $callable) = $this->_normalize_arguments($middleware, $name);
    $this->_stack = array_merge(array($name => $callable), $this->_stack);
  }
  
  public function prepend($middleware, $name = null)
  {
    return $this->unshift($middleware, $name);
  }
  
  public function shift()
  {
    return array_shift($this->_stack);
  }
  
  
  protected function _insert($position, $offset, $middleware, $name = null)
  {
    $names = array_keys($this->_stack);
    $callables = array_values($this->_stack);
    
    $pos = array_search($offset, $names);
    
    if ($position == Pile_Stack::BEFORE) {
      $pos--;
      $pos = $pos >= 0 ? $pos : false;
    }
    
    if (false == $pos AND $position == Pile_Stack::BEFORE) {
      return $this->unshift($middleware, $name);
    }
    if ( ((count($this->_stack) - 1) == $pos || false === $pos) && $position == Pile_Stack::AFTER) {
      return $this->push($middleware, $name);
    }
    
    list($name, $callable) = $this->_normalize_arguments($middleware, $name);
    
    $names_after = array_splice($names, $pos);
    $callables_after = array_splice($callables, $pos);
    
    $names[] = $name;
    $callables[] = $callable;
    
    $this->_stack = array_merge(array_combine($names, $callables), array_combine($names_after, $callables_after));
  }
  
  protected function _normalize_arguments($middleware, $name = null)
  {
    $callable = $this->_normalize_to_callable($middleware);
    if (null == $name) {
      $name = $this->_create_name($callable);
    }
    return array($name, $callable);
  }
  
  protected function _normalize_to_callable($middleware)
  {
    if (is_callable($middleware)) {
      return $middleware;
    }
    
    if (   ( ( is_string($middleware) && class_exists($middleware) ) || is_object($middleware) )
        && method_exists($middleware, 'call')) {
      return array($middleware, 'call');
    }
    
    throw new InvalidArgumentException('Unable to mormalize argument to callable');
  }
  
  protected function _name($callable)
  {
    if (is_string($callable)) {
      return $callable;
    }
    
    if (is_array($callable)) {
      list($object, $method) = $callable;
      if (is_string($object)) {
        return $object;
      }
      return get_class($object);
    }
    
    throw new InvalidArgumentException('Unable to build string representation of middleware');
  }
  
  
  /*
   * ArrayAccess methods
   */
  
  public function offsetExists($offset)
  {
    return array_key_exists($offset, $this->_stack);
  }
  
  public function offsetGet($offset)
  {
    return isset($this->_stack[$offset]) ? $this->_stack[$offset] : null;
  }
  
  public function offsetSet($offset, $middleware)
  {
    if (null === $offset) {
      $this->_stack[] = $middleware;
    } else {
      $this->_stack[$offset] = $middleware;
    }
  }
  
  public function offsetUnset($offset)
  {
    unset($this->_stack[$offset]);
  }
  
  
  /*
   * Iterator methods
   * 
   * As Pile_Stack is just a wrapper around the $_stack array,
   * iterator implementation is reduced to the minimal.
   */
  
  public function current()
  {
    return current($this->_stack);
  }
  
  public function key()
  {
    return key($this->_stack);
  }
  
  public function next()
  {
    return next($this->_stack);
  }
  
  public function rewind()
  {
    return reset($this->_stack);
  }
  
  public function valid()
  {
    return true;
  }
}
