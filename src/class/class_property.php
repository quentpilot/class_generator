<?php

require_once('class_items.php');

class class_property extends class_items
{
  public function load()
  {
    $output = array();

    foreach ($this->get_all() as $key => $value)
    {
      $value = (is_string($value) && $value != 'NULL') ? "'$value'" : $value;

      $output[] = "public $key = $value;\n";
    }

    $output = implode("\n\t", $output);
    $this->output = $output;

    return $output;
  }
}
