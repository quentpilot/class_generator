<?php

require_once('class_entities.php');

class class_methods extends class_entities
{
  public function output($key, $value, $delim = '', $implode = '')
  {
    $output = "";
    $params = "";
    $body = "";

    if (is_array($value))
    {
      $this->prepare($value, $delim, $implode);
    }

    $output .= "public function $key(".$value['params'].")" . $delim . $implode . '{';
    $output .= $delim . $implode . $implode . $value['body'] . $delim . $implode . '}' . $delim;

    $this->output = $output;

    return $output;
  }

  public function prepare(&$value, $delim, $implode)
  {
    if (!empty($value['params']))
    {
      $value['params'] = implode(', ', $value['params']);
    }

    if (!empty($value['body']))
    {
      $value['body'] = implode($delim.$implode.$implode, $value['body']);
    }

    if (!empty($value['extends']))
    {
      $value['extends'] = ' extends ' . $value['extends'];
    }

    if (!empty($value['implements']))
    {
      $value['implements'] = ' implements ' . $value['implements'];
    }

    $this->config = $value;
    return $this;
  }
}
