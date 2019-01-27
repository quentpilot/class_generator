<?php

require_once(__DIR__.'/../../class/class_generator.php');
require_once(__DIR__.'/../../class/class_drivers.php');

class php_class_generator extends class_generator implements iclass_driver
{
  public function load()
  {
    parent::load();
    return $this->result("<?php\n".$this->result."\n?>");
  }
}
