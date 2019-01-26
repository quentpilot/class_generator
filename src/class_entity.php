<?php

require_once('class_attributes.php');
require_once('class_methods.php');

interface iclass_entity
{
  public function load();
  public function attributes();
  public function methods();
}

class class_entity implements iclass_entity
{
  // class / abstract / interface
  public $type = NULL;

  // classname
  public $name = NULL;

  //option to add an extended class string
  public $extends = NULL;

  // option to add implemented classes string
  public $implements = NULL;

  // class attributes generator
  protected $attributes = NULL;

  // class methods generator
  protected $methods = NULL;

  // public render view
  public $result_html = NULL;

  // file put content
  public $result_php = NULL;

  // result attributes prefix
  protected $result_prefix = 'result_';

  // allowed class attributes used to store final result by type
  protected $allowed_results_types = array('php', 'html');

  // render delimiter character option following result type generating
  protected $results_types_delimiters = array('html' => "<br/>", 'php' => "\n");

  // render delimiter character option following result type generating
  // used to implode each entity output result
  protected $results_types_implodes = array('html' => "", 'php' => "\t");

  // call sub-generators to build anything between class brackets
  protected $allowed_generators = array('attributes', 'methods');

  // custom class methods to call before and after building result through format() method
  // usually used to not modify default generation algorithm
  protected $routines = array('before' => array(), 'while' => array('generate_crud', 'generate_accessor'), 'after' => array('format_html'));

  // global result
  public $result = array();


  public function __construct($name = NULL, $extends = NULL, $implements = NULL, $type = NULL, $attributes = array(), $methods = array())
  {
    $this->name = $name;
    $this->extends = $extends;
    $this->implements = $implements;
    $this->type = $type;
    $this->attributes = new class_attributes($attributes);
    $this->methods = new class_methods($methods);
    $this->set_generators($attributes);
  }

  public static function factory($name = array(), $extends = NULL, $implements = NULL, $type = NULL, $generators = NULL)
  {
    $entity = array();
    $name = is_string($name) ? array($name) : $name;

    if (is_array($name))
    {
      $entity = array();
      foreach ($name as $it => $class)
      {
        $classname = get_called_class();
        $entity[$it] = new $classname($class, $extends, $implements, $type);
        $entity[$it]->set_generators($generators);
      }
    }


    $entity = (count($name) == 1) ? $entity[0] : $entity;
    return $entity;
  }

  public function load()
  {
    if (is_string($this->name))
    {
      $allowed = $this->allowed_results_types;

      // loop for each allowed type to set related result
      foreach ($allowed as $it => $type)
      {
        $output = array();
        $result = $this->result_prefix . $type;

        if (property_exists($this, $result))
        {
          // get delimiters following result type
          $delim = $this->delim($type);
          $implode = $this->implode($type);

          $this->format($output, $type, $delim, $implode);

          // format and set current result attribute
          if (count($output) > 1)
          {
            $output = implode($delim . $implode, $output);
            $this->result($type, $output);
          }
        }
      }
    }
    return $this;
  }

  protected function format(&$output, $type, $delim, $implode)
  {
    $output[0] = '';

    // build class header
    if ($type === 'php')
    {
      $output[0] .= "<?php" . $delim;
    }

    $output[0] .= $this->classname($delim);

    if (!empty($this->allowed_generators))
    {
      $this->load_generators($output, $delim, $implode);
    }

    // build class footer
    $output[] = $delim . '}';

    if ($type === 'php')
    {
      $output[count($output) - 1] .= $delim . '?>';
    }

    return $this;
  }

  protected function load_generators(&$output, $delim, $implode)
  {
    $generators = $this->allowed_generators;

    if (!empty($generators) && is_array($generators))
    {
      foreach ($generators as $engine)
      {
        $output[] = $this->output($engine, $delim, $implode);
      }
    }

    return $this;
  }

  protected function output($type, $delim, $implode, $callback = 'output')
  {
    $output = array("");

    if (method_exists($this, $type))
    {
      $engine = $this->$type();

      if ($engine instanceof iclass_entities)
      {
        $entities = $engine->get_all();

        foreach ($entities as $key => $value)
        {
          if (method_exists($engine, $callback))
          {
            // build wanted output format method from current class engine
            $output[] = $engine->$callback($key, $value, $delim, $implode);
          }
        }
      }
    }

    $output = implode($delim . $implode, $output);
    return $output;
  }

  protected function classname($delim)
  {
    $output = $this->type . 'class ' . $this->name;
    $output .= $this->extends . $this->implements;
    $output .= $delim . '{';

    return $output;
  }

  protected function delim($type)
  {
    if (!empty($this->results_types_delimiters[$type]))
    {
      return $this->results_types_delimiters[$type];
    }
    return '';
  }

  protected function implode($type)
  {
    if (!empty($this->results_types_implodes[$type]))
    {
      return $this->results_types_implodes[$type];
    }
    return '';
  }

  public function result($type = '', $value = NULL)
  {
    $result = $this->result_prefix . $type;

    if (property_exists($this, $result) && $result !== 'result')
    {
      if (!empty($value))
      {
        $this->{$result} = $value;
        $this->result[$this->name][$type] = $value;
      }
      return $this->{$result};
    }

    return NULL;
  }

  protected function routines($type, $params = array(), $valid = FALSE, $implode = FALSE)
  {
    $routines = $this->routines;
    $status = array();

    // call wanted methods
    if (!empty($routines[$type]))
    {
      if (is_array($routines[$type]))
      {
        foreach ($routines[$type] as $routine)
        {
          if (method_exists($this, $routine))
          {
            $status[$routine] = call_user_func_array(array($this, $routine), $params);
          }
        }
      }
    }

    // implode status
    if ($implode && is_string($implode) && !empty($status))
    {
      $status = implode($implode, $status);
    }

    // invalid empty results
    if ($valid)
    {
      if (empty($status))
      {
        $status = FALSE;
      }
      elseif (is_string($status) && $implode)
      {
        $status = TRUE;
      }
      else
      {
        $calls = $status;
        $status = TRUE;

        foreach ($calls as $routine => $result)
        {
          if (empty($result))
          {
            $status = FALSE;
          }
        }
      }
    }

    return $status;
  }

  public function set_generators($generators)
  {
    if (!empty($generators) && is_array($generators))
    {
      foreach ($generators as $classname => $generator)
      {
        if (is_object($generator))
        {
          if ($generator instanceof iclass_entities)
          {
            if ($generator instanceof class_attributes)
            {
              $this->attributes = $generator;
            }
            elseif ($generator instanceof class_methods)
            {
              $this->methods = $generator;
            }
          }
        }
      }
    }
    return $this;
  }

  public function attributes($property = NULL)
  {
    if (!is_null($property))
    {
      return $this->attributes->get($property);
    }
    return $this->attributes;
  }

  public function methods($name = NULL)
  {
    if (!is_null($name))
    {
      return $this->methods->get($name);
    }
    return $this->methods;
  }
}
