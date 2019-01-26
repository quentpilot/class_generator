<?php

require_once(__DIR__.'/class_entities.php');

class class_attributes extends class_entities
{
  public function output($key, $value, $delim = '', $implode = '')
  {
    $output = "";

    $value = is_string($value) ? "'$value'" : $value;

    $output = "public $key = $value;$delim";

    $this->output = $output;

    return $output;
  }
}
