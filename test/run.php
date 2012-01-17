<?php
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
$basedir = dirname(__FILE__).DS;
require_once $basedir.'utils'.DS.'tests.php';

if(!defined('TESTS_DOC_ROOT'))
{
  # 1. CONFIG file is required
  $config_file = $basedir.'config.php';
  if(file_exists($config_file))
  {
    include $config_file;
    $doc_root = $config['pile_base_url']."/";
  }
  else
  {
    echo <<<OUTPUT

ERROR: MISSING CONFIG FILE FOR TESTS
====================================

In order to run test, you must have a valid test/config.php file.
Please copy test/config.php.dist into test/config.php and
set required values.

The \$config['pile_base_url'] is required to run functional tests.

NOTE: the Pile source code must be somewhere in your HTTP server public
folder in order to call testing pile apps.

---

OUTPUT;
  exit;
  }
  
  # 2. HTTP+CURL requirements
  if(function_exists('curl_version'))
  {
    $url = $doc_root.'test'.DS.'apps'.DS.'index.php';
    $response = test_request($url, 'GET');
    if($response)
    {
      $v = phpversion();
      $curl_v = curl_version();
      $cv = $curl_v['version'];
      if($response == $v)
      {

        echo <<<OUTPUT

==== RUNNING PILE TESTS [PHP $v — cURL $cv] =====

OUTPUT;
      } else {
        echo  <<<OUTPUT

ERROR: Wrong response to HTTP request test
==========================================

Requesting $url
must return '$v' but returns this response:

$response

---

Your \$config['pile_base_url'] might be wrong or maybe it's your HTTP
server configuration and/or php installation.
Please fix it in order to run tests.

---

OUTPUT;
        exit;
      }
    }
    else
    {
      exit;
    }
    
  }
  else
  {
    echo <<<OUTPUT

ERROR: cURL Library is required
===============================

Please install PHP cURL library in order to run Pile tests.


---

OUTPUT;
  }
   
  
  define('TESTS_DOC_ROOT', $doc_root);
}

require $basedir.'phpile_test.php';

