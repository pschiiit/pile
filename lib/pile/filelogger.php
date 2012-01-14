<?php

class PileFileLogger
{
  public function __construct($file_path)
  {
    $this->path = $file_path;
  }
  
  public function write($str)
  {
    file_put_contents($this->path, $str . "\n", FILE_APPEND);
  }
}

