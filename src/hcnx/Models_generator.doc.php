<?php

class Models_generator extends CI_Controller
{
  // database name GET param
  public $dbname = NULL;

  // table name GET param
  public $tbname = NULL;

  //option to add an extended class string
  public $extends = 'extends bdd';

  // option to add implemented classes string
  public $implements = '';

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

  protected $save_path = __DIR__ . '/../libraries/bdd/';

  // ORM class attributes to generate
  protected $primary = NULL;
  protected $prefix = NULL;
  protected $fields = NULL;
  protected $fields_str = NULL;
  protected $fields_null = NULL;

  /**
   * @load_base_frame(no_menu)
   * @access_restrict()
   */
  public function index()
  {
      echo 'ok';
  }

  public function generate_all($db = NULL)
  {
    if (is_null($db)) {
      //$db = config_item('database');
      $db = 'grattofolies';
    }
    // get all tables name
    $sql = "SELECT table_name FROM information_schema.tables where table_schema='$db'";
    $tables = $this->db->query($sql)->result();

    if (!empty($tables))
    {
      foreach ($tables as $table)
      {
        $this->generate($db, $table->table_name);
      }
    }
  }

  public function generate($db = NULL, $table = NULL)
  {
    if (is_null($db)) {
      //$db = config_item('database');
      $db = 'grattofolies';
    }

    if (is_null($table))
    {
      $this->generate_all($db);
      //echo "Les paramètres 'nom_bdd/nom_table' doivent être renseignés";
    }
    else
    {
      // set db config
      $this->dbname = $db;
      $this->tbname = $table;

      //get related db table infos
      $sql = "DESCRIBE `".$db."`.`".$table."`";
      $query = $this->db->query($sql);
      $result = $query->result_array();

      // build data from sql result
      $this->build($result);

      // print html render
      echo $this->result();

      // save new class if file does not exists
      // or rename and save it to keep existing file
      $this->save();
    }
  }

  protected function build($result = array())
  {
    $prim = '';
    $prefix = '';
    $fields = array();
    $fields_str = array();
    $null = array();
    $filedata = '';

    // get tablename prefix
    if (substr($this->tbname, 3, 1) == "_")
    {
      $prefix = substr($this->tbname, 0, 4);
    }

    if (!empty($result))
    {
      // loop for each table field
      for ($i = 0 ; $i < count($result) ; $i++)
      {
        // remove prefix
        $field = str_replace($prefix, '', $result[$i]['Field']);
        $type = str_replace($prefix, '', $result[$i]['Type']);

        // get primary key
        if ($result[$i]['Key'] == 'PRI')
        {
          $prim = $field;
        }

        // format an array of fields name and related type
        $fields[$field] = $type;
        $fields_str[] = "'" . $field . " ' => '" . $type. "'";

        // build an array of nullable fields name
        if ($result[$i]['Null'] == 'YES')
        {
          $null[] = $field;
        }
      }
    }

    // save table infos
    $this->primary = $prim;
    $this->prefix = $prefix;
    $this->fields = $fields;
    $this->fields_str = $fields_str;
    $this->fields_null = $null;

    // format as a string following allowed results type
    $this->format();

    return $this;
  }

  protected function format()
  {
    // pre generation
    $this->routines('before');

    $extends = $this->extends;
    $implements = $this->implements;
    $allowed = $this->allowed_results_types;
    $delimiters = $this->results_types_delimiters;

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
        $output[] = '<?php' . $delim . 'class ' . $this->tbname ." $extends $implements {" . $delim;
        $output[] = $delim . "\tvar $" . implode(";$delim\tvar $", array_keys($this->fields)) . ';' . $delim;
        $output[] = $delim . "\t" . 'var $table_prefix = \'' . $this->prefix . '\';';
        $output[] = $delim . "\t" . 'var $_table = \'' . str_replace($this->prefix, '', $this->tbname) . '\';';
        $output[] = $delim . "\t" . 'var $_primary_key = \'' . $this->primary . '\';';
        $output[] = $delim . "\t" . 'var $_champs = array(' . implode(', ', $this->fields_str) . ');';
        $output[] = $delim . "\t" . 'var $_null = array(\'' . implode('\', \'', $this->fields_null) . '\');' . $delim;
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

  protected function save()
  {
    $path = $this->save_path;
    $path = __DIR__ . '/../libraries/bdd/';
    $filename = $this->tbname.'.php';

    if (!is_null($path))
    {
      if (file_exists($path) && is_dir($path))
      {
        if (file_exists($path.$filename))
        {
          $filename = 'new_' . $filename;
        }

        return file_put_contents($path.$filename, $this->result('php'));
      }
    }

    return FALSE;
  }

  protected function result($type = 'html')
  {
    $type = is_null($type) ? 'html' : $type;
    $attr = 'result_' . $type;

    if (property_exists($this, $attr))
    {
      return $this->{$attr};
    }

    return 'NULL';
  }

  /*******************************************************************************************
  *                                                                                          *
  *                  Custom Methods Callable Before and After Generation                     *
  *                                                                                          *
  *******************************************************************************************/

  /**
  * before building format()
  */

  /**
  * while building format()
  */

  protected function generate_methods($delim, $type)
  {
    $output = array();

    /** build CRUD functions **/

    /**
    * build get($id)
    */
    /***
    $output[] = $delim . "\t" . 'public function get($id = 0)';
    $output[] = $delim . "\t" . '{';


    // for CI db class builder
    $output[] = $delim . "\t\t" . 'return $this->db->set($id)->get()->result();';

    // for hcnx bdd class orm
    $output[] = $delim . "\t\t" . 'return $this->get($id);';
    $output[] = $delim . "\t" . '}';
    */

    $output = implode('', $output);
    return $output;
  }

  protected function generate_crud($delim, $type)
  {
    $output = array();

    $output = implode('', $output);
    return $output;
  }

  protected function generate_accessor($delim, $type)
  {
    $output = array();

    /** build CRUD functions **/

    /**
    * build get($id)
    */

    $output[] = $delim . "\t" . 'public function get($id = 0)';
    $output[] = $delim . "\t" . '{';


    // for CI db class builder
    //$output[] = $delim . "\t\t" . 'return $this->db->set($id)->get()->result();';

    // for hcnx bdd class orm
    $output[] = $delim . "\t\t" . 'return $this->get($id);';
    $output[] = $delim . "\t" . '}' . $delim;


    /**
    * build get($id)
    */

    $output[] = $delim . "\t" . 'public function set($id = NULL, $data = array())';
    $output[] = $delim . "\t" . '{';

    $output[] = $delim . "\t\t" . 'return $this->edit($id, $data);';
    $output[] = $delim . "\t" . '}' . $delim;


    $output = implode('', $output);
    return $output;
  }

  /**
  * after building format()
  */

  protected function format_html()
  {
    $this->result_html .= "<br/><br/>";
    return true;
  }


}

/*******************************************************************************************
*                     Query For The Future, To Generate Every Tables                       *
*                                                                                          *
*                                                                                          *
* USE information_schema;                                                                  *                                                                                          *
* SELECT TABLE_NAME 'Table', COLUMN_NAME 'Field', COLUMN_TYPE 'Type', IS_NULLABLE 'Null',  *
* COLUMN_KEY 'Key', COLUMN_DEFAULT 'Default', EXTRA 'Extra'                                *
* FROM information_schema.columns                                                          *
* WHERE table_schema = 'grattofolies'                                                      *
* ORDER BY TABLE_NAME;                                                                     *
*                                                                                          *
*******************************************************************************************/

// $sql = "
//     SELECT TABLE_NAME 'Table', COLUMN_NAME 'Field', COLUMN_TYPE 'Type',
//            IS_NULLABLE 'Null', COLUMN_KEY 'Key', COLUMN_DEFAULT 'Default', EXTRA 'Extra'
//     FROM information_schema.columns
//     WHERE table_schema = 'grattofolies'
//     ORDER BY TABLE_NAME;
// ";
//
// $query = $this->db->query($sql);
// $res = $query->result_array();

// foreach ($res as $it => $table) {
//   $result = array();
//   $tablename = $table['Table'];
//
//   // gonna fill each table with related infos
//   if (in_array($tablename, $tables))
//   {
//     $result[$tablename] = array(
//       'Field' => $table['Field'],
//       'Type' => $table['Type'],
//       'Null' => $table['Null'],
//       'Key' => $table['Key'],
//       'Default' => $table['Default'],
//       'Extra' => $table['Extra']
//     );
//
//     //$result[$tablename] = $table;
//     print_r($result[$tablename]);die;
//     //$this->build($result[$tablename]);
//   }
// }
