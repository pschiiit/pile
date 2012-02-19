<?php

require_once $libdir . DS . 'Pile' . DS . 'Stack.php';

test_case("Stack");
  test_case_describe("Pile_Stack unit tests");
  
  function test_stack_construct()
  {
    $s = new Pile_Stack();
    assert_identical(array(
     'head'            => 'pile_middleware_head',
     'method_override' => 'pile_middleware_method_override',
     ), $s->dump());
    
    $s = new Pile_Stack(false);
    assert_identical(array(), $s->dump());
  }
  
  function test_stack_array_access()
  {
    $s = new Pile_Stack();
    
    // offsetExists
    assert_true(isset($s['head']));
    
    // offsetGet
    assert_equal('pile_middleware_head', $s['head']);
    
    // offsetSet
    $s['head'] = 'pile_middleware_head-modified';
    assert_equal('pile_middleware_head-modified', $s['head']);
    
    // offsetUnset
    unset($s['head']);
    assert_false(isset($s['head']));
  }
  
  function test_stack_iterator()
  {
    $s = new Pile_Stack();
    
    $stack = $s->dump();
    $iterated_stack = array();
    foreach ($s as $key => $value) {
      $iterated_stack[$key] = $value;
    }
    
    assert_identical($iterated_stack, $stack);
  }
  
  function test_stack_push()
  {
    $s = new Pile_Stack();
    $s->push('var_dump', 'dump');
    
    assert_identical(array(
     'head'            => 'pile_middleware_head',
     'method_override' => 'pile_middleware_method_override',
     'dump'            => 'var_dump'
     ), $s->dump());
  }
  
  function test_stack_append()
  {
    $s = new Pile_Stack();
    $s->append('var_dump', 'dump');
    
    assert_identical(array(
     'head'            => 'pile_middleware_head',
     'method_override' => 'pile_middleware_method_override',
     'dump'            => 'var_dump'
     ), $s->dump());
  }
  
  function test_stack_pop()
  {
    $s = new Pile_Stack();
    $s->push('var_dump', 'dump');
    
    assert_identical('var_dump', $s->pop());
  }
  
  function test_stack_unshift()
  {
    $s = new Pile_Stack();
    $s->unshift('var_dump', 'dump');
    
    assert_identical(array(
     'dump'            => 'var_dump',
     'head'            => 'pile_middleware_head',
     'method_override' => 'pile_middleware_method_override'
     ), $s->dump());
  }
  
  function test_stack_prepend()
  {
    $s = new Pile_Stack();
    $s->prepend('var_dump', 'dump');
    
    assert_identical(array(
     'dump'            => 'var_dump',
     'head'            => 'pile_middleware_head',
     'method_override' => 'pile_middleware_method_override'
     ), $s->dump());
  }
  
  function test_stack_shift()
  {
    $s = new Pile_Stack();
    $s->unshift('var_dump', 'dump');
    
    assert_identical('var_dump', $s->shift());
  }
  
  function test_stack_replace()
  {
    $s = new Pile_Stack();
    $s->replace('head', 'var_dump');
    
    assert_identical(array(
     'head'            => 'var_dump',
     'method_override' => 'pile_middleware_method_override'
     ), $s->dump());
  }
  
  function test_stack_insert_before()
  {
    $s = new Pile_Stack();
    $s->insert_before('method_override', 'var_dump', 'dump');
    
    assert_identical(array(
     'head'            => 'pile_middleware_head',
     'dump'            => 'var_dump',
     'method_override' => 'pile_middleware_method_override'
     ), $s->dump());
  }
  
  function test_stack_insert_before_limit()
  {
    $s = new Pile_Stack();
    $s->insert_before('head', 'var_dump', 'dump');
    
    assert_identical(array(
     'dump'            => 'var_dump',
     'head'            => 'pile_middleware_head',
     'method_override' => 'pile_middleware_method_override'
     ), $s->dump());
  }
  
  function test_stack_insert_before_not_found()
  {
    $s = new Pile_Stack();
    $s->insert_before('gloubiboulga', 'var_dump', 'dump');
    
    assert_identical(array(
     'dump'            => 'var_dump',
     'head'            => 'pile_middleware_head',
     'method_override' => 'pile_middleware_method_override'
     ), $s->dump());
  }
  
  function test_stack_insert_after()
  {
    $s = new Pile_Stack();
    $s->insert_after('head', 'var_dump', 'dump');
    
    assert_identical(array(
     'head'            => 'pile_middleware_head',
     'dump'            => 'var_dump',
     'method_override' => 'pile_middleware_method_override'
     ), $s->dump());
  }
  
  function test_stack_insert_after_limit()
  {
    $s = new Pile_Stack();
    $s->insert_after('method_override', 'var_dump', 'dump');
    
    assert_identical(array(
     'head'            => 'pile_middleware_head',
     'method_override' => 'pile_middleware_method_override',
     'dump'            => 'var_dump'
     ), $s->dump());
  }
  
  function test_stack_insert_after_not_found()
  {
    $s = new Pile_Stack();
    $s->insert_after('gloubiboulga', 'var_dump', 'dump');
    
    assert_identical(array(
     'head'            => 'pile_middleware_head',
     'method_override' => 'pile_middleware_method_override',
     'dump'            => 'var_dump'
     ), $s->dump());
  }
  
  function test_stack_normalize_function_name()
  {
    $s = new Pile_Stack();
    $s->push('var_dump');
    
    assert_identical(array(
     'head'            => 'pile_middleware_head',
     'method_override' => 'pile_middleware_method_override',
     'var_dump'         => 'var_dump'
     ), $s->dump());
  }
  
  function test_stack_normalize_static_method_name()
  {
    class StaticFoo
    {
      public static function bar()
      {
        return true;
      }
    }
    
    $s = new Pile_Stack();
    $s->push(array('StaticFoo', 'bar'));
    
    assert_identical(array(
     'head'            => 'pile_middleware_head',
     'method_override' => 'pile_middleware_method_override',
     'StaticFoo::bar'  => array('StaticFoo', 'bar')
     ), $s->dump());
  }
  
  function test_stack_normalize_instance_method_name()
  {
    class Foo
    {
      public function bar()
      {
        return true;
      }
    }
    $foo = new Foo();
    
    $s = new Pile_Stack();
    $s->push(array($foo, 'bar'));
    
    assert_identical(array(
     'head'            => 'pile_middleware_head',
     'method_override' => 'pile_middleware_method_override',
     'Foo::bar'        => array($foo, 'bar')
     ), $s->dump());
  }
  
  function test_stack_normalize_static_method()
  {
    class StaticBar
    {
      public static function call()
      {
        return true;
      }
    }
    
    $s = new Pile_Stack();
    $s->push('StaticBar');
    
    assert_identical(array(
     'head'            => 'pile_middleware_head',
     'method_override' => 'pile_middleware_method_override',
     'StaticBar::call' => array('StaticBar', 'call')
     ), $s->dump());
  }
  
  function test_stack_normalize_instance_method()
  {
    class Bar
    {
      public function call()
      {
        return true;
      }
    }
    $bar = new Bar();
    
    $s = new Pile_Stack();
    $s->push($bar);
    
    assert_identical(array(
     'head'            => 'pile_middleware_head',
     'method_override' => 'pile_middleware_method_override',
     'Bar::call'       => array($bar, 'call')
     ), $s->dump());
  }
  
end_test_case();
