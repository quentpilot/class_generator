<?php

interface iclass_entity
{
  public function load();
  public function attributes();
  public function methods();
}

class class_entity implements iclass_entity
{
  public $configs = NULL;

  // class / abstract / interface
  protected $type = NULL;

  //option to add an extended class string
  protected $extends = NULL;

  // option to add implemented classes string
  protected $implements = NULL;

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

  // custom class methods to call before and after building result through format() method
  // usually used to not modify default generation algorithm
  protected $routines = array('before' => array(), 'while' => array('generate_crud', 'generate_accessor'), 'after' => array('format_html'));

  protected $result = NULL;



  public function __construct($database = NULL, $datatable = NULL)
  {
    $this->database = $database;
    $this->datatable = $datatable;
    $this->attributes = new class_attributes();
    $this->methods = new class_methods();
  }

  public function load($configs = NULL)
  {
    $this->configs = $configs;
    $this->orm_attributes();
    // check if run one or all tables
    // get table description
    // build attributes
    // build methods
    // print
    // save
  }

  protected function format()
  {
    $allowed = $this->allowed_results_types;
    $attributes = $this->attributes()->get_all();
    $methodes = $this->methods()->get_all();

    // loop for each allowed type to set related result
    foreach ($allowed as $it => $type)
    {
      $output = array();
      $result = $this->result_prefix . $type;

      if (property_exists($this, $result))
      {
        // get delimiter following result type
        $delim = $this->delim($type);

        // build class header
        $output[] = $this->classname($delim);

        // build attributes
        foreach ($attributes as $key => $value)
        {
          $value = is_string($value) ? "'$value'" : $value;

          $output[] = "public $key = $value";
        }

        // build methods
        foreach ($methods as $key => $value)
        {
          $value = is_array($value) ? implode("", $value) : $value;

          $output[] = "public function $key() $delim { $delim $value $delim }";
        }

        $output[] = $delim . '}';

        //format and set current result attribute
        $this->{$result} = implode($delim . "\t", $output);
      }

    }

    return $this;
  }

  protected function classname($delim)
  {
    $output = '<?php' . $delim . $this->type . ' ' . $this->datatable . $this->extends . $this->implements .' {' . $delim;
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

  protected function result($type = '')
  {
    $result = $this->result_prefix . $type;

    if (property_exists($this, $result))
    {
      return $this->$result;
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

  public function attributes($property = NULL)
  {
    if (!is_null($attributes))
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


  /*******************************************************************************************
  *                                                                                          *
  *                  Custom Methods Callable Before and After Generation                     *
  *                                                                                          *
  *                                                                                          *
  *                                                                                          *
  *                                                                                          *
  *                                                                                          *
  *******************************************************************************************/

  protected function orm_attributes()
  {
    $configs = $this->configs;
    $prim = '';
    $prefix = '';
    $fields = array();
    $fields_str = array();
    $fields_null = array();
    $filedata = '';

    // get datatable prefix
    if (substr($this->datatable, 3, 1) == "_")
    {
      $prefix = substr($this->datatable, 0, 4);
    }

    if (!empty($configs))
    {
      // loop for each table field
      for ($i = 0 ; $i < count($configs) ; $i++)
      {
        // remove prefix
        $field = str_replace($prefix, '', $configs[$i]['Field']);
        $type = str_replace($prefix, '', $configs[$i]['Type']);

        // get primary key
        if ($configs[$i]['Key'] == 'PRI')
        {
          $prim = $field;
        }

        // format an array of fields name and related type
        $fields[$field] = $type;
        $fields_str[] = "'" . $field . " ' => '" . $type. "'";

        // build an array of nullable fields name
        if ($configs[$i]['Null'] == 'YES')
        {
          $fields_null[] = $field;
        }
      }
    }

    $this->attributes->add('primary', $prim)
                     ->add('prefix', $prefix)
                     ->add('fields', $fields)
                     ->add('fields_str', $fields_str)
                     ->add('fields_null', $fields_null);


    return $this->format_orm_attributes();
  }

  protected function format_orm_attributes()
  {
    // pre generation
    $this->routines('before');

    $extends = $thisextends;
    $implements = $this->implements;
    $allowed = $this->allowed_results_types;
    $delimiters = $this->results_types_delimiters;
    $fields = $this->attributes()->get('fields');
    $fields_str = $this->attributes()->get('fields_str');
    $fields_null = $this->attributes()->get('fields_null');

    // loop for each allowed type to set related result
    foreach ($allowed as $it => $type)
    {
      $output = array();
      $attr = $this->result_prefix . $type;

      if (property_exists($this, $attr) && !empty($delimiters[$type]))
      {
        // get delimiter following result type
        $delim = $delimiters[$type];

        // build PHP class
        $output[] = '<?php' . $delim . 'class ' . $this->datatable ." $extends $implements {" . $delim;
        $output[] = $delim . "\tvar $" . implode(";$delim\tvar $", array_keys($fields)) . ';' . $delim;
        $output[] = $delim . "\t" . 'var $table_prefix = \'' . $prefix . '\';';
        $output[] = $delim . "\t" . 'var $_table = \'' . str_replace($prefix, '', $this->datatable) . '\';';
        $output[] = $delim . "\t" . 'var $_primary_key = \'' . $primary . '\';';
        $output[] = $delim . "\t" . 'var $_champs = array(' . implode(', ', $fields_str) . ');';
        $output[] = $delim . "\t" . 'var $_null = array(\'' . implode('\', \'', $fields_null) . '\');' . $delim;
        $output[] = $this->routines('while', array($delim, $type), false, " ");
        $output[] = $delim . '}';

        //set current result attribute
        $this->{$attr} = implode('', $output);
      }
    }

    // post generation
    $this->routines('after');

    return $this;
  }

}
