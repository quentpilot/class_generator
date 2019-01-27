<?php

require_once('class_items.php');

class class_method extends class_items
{
  public function load()
  {
    $output = array();

    foreach ($this->get_all() as $key => $value)
    {
      $output[] = "public function $key(".$value['params'].")\n\t{";
      $output[] = "\t\t" . $value['body'] . "\n\t}\n";
    }

    $output = implode("\n\t", $output);
    $this->output = $output;
    return $output;
  }
}
