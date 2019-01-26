<?php

require_once(__DIR__ . '/../../../../lib/generator/class/class_generator.php');
require_once('datatable_entity.php');

class datatable_generator extends class_generator
{

  public $db = NULL;

  public $database = NULL;

  public $datatable = NULL;

  public $query = NULL;

  public $result = NULL;

  public function __construct($database = NULL, $datatable = NULL)
  {
    $this->database = $database;
    $this->datatable = $datatable;

    $this->db = mysqli_connect("localhost","root","root","grattofolies");

    // Check connection
    if (mysqli_connect_errno())
    {
      echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
  }

  public function run()
  {
    $status = FALSE;

    if (!empty($this->database) && is_string($this->database))
    {
      if (is_null($this->datatable))
      {
        $this->load_datatables($tables);

        if (!empty($tables))
        {
          $status = TRUE;

          foreach ($tables as $table)
          {
            $this->datatable = $table['name'];

            if (!$this->generate())
            {
              $status = FALSE;
            }
          }
        }
      }
      elseif (is_string($this->datatable))
      {
        if (!$this->generate())
        {
          $status = FALSE;
        }
      }
    }

    return $status;
  }

  protected function load_datatables(&$datatables = array(), $database = NULL, $type = 'object')
  {
    $database = is_null($database) ? $this->database : $database;

    $sql = "SELECT table_name as 'name' FROM information_schema.tables where table_schema='$database'";
    $datatables = $this->query($sql, $type);
    return $datatables;
  }

  protected function load_datatable(&$datatable = NULL, $database = NULL, $type = 'array')
  {
    $database = is_null($database) ? $this->database : $database;
    $datatable = is_null($datatable) ? $this->datatable : $datatable;
    $table = NULL;

    if (!is_null($database) && !is_null($datatable))
    {
      $sql = "DESCRIBE `$database`.`$table`";
      $table = $this->query($sql, $type);
    }

    return $table;
  }

  public function query($sql = NULL, $result_type = 'array', &$result = NULL)
  {
    $result = ($result_type === 'object') ? NULL : array();

    // request
    if ($this->db instanceof mysqli)
    {
      if (!$results = $this->db->query($sql))
      {
        return $result;
      }

      if ($results->num_rows > 0)
      {

        $_results = array();
        $it = 0;

        while ($_result = $results->fetch_assoc())
        {
          if ($result_type === 'array')
          {
            array_push($_results, $_result);
            $it++;
          }
          elseif ($result_type === 'object')
          {
            //$_results[] = (object)$_result;
            $this->array_to_object($_result);

            array_push($_results, $_result);
            $it++;
          }
        }

        $result = $_results;
      }

      //print_r($query->fetch_assoc());print_r($query->fetch_assoc());die;
      //print_r($_results);die;
    }
    // format result

    return $result;
  }

  protected function prepare(&$configs = NULL)
  {
    $this->load_datatable($configs);

    if (!empty($configs))
    {
      return $configs;
    }

    return NULL;
  }

  protected function generate()
  {
    if ($this->prepare($configs))
    {
      $entity = $this->new_entity();
      $entity->load($configs);
      $this->add($entity);
      return true;
    }
    return false;
  }

  protected function class($class = NULL)
  {
    if (is_array($class) && !empty($class))
    {
      $status = TRUE;

      foreach ($class as $entity)
      {
        if (!($entity instanceof iclass_entity))
        {
          $status = FALSE;
        }
      }

      $this->class = ($status) ? $class : $this->class;
    }
    elseif ($class instanceof iclass_entity)
    {
      $this->class = $class;
    }

    return $this->class;
  }

  public function add($entity)
  {
    $this->entities[] = $entity;
    return $this;
  }

  public function array_to_object($array, &$object = NULL)
  {
    $object = new datatable_entity();

    if (!empty($array))
    {
      foreach ($array as $key => $value)
      {
        $object->{$key} = $value;
      }
    }

    return $object;
  }

}
