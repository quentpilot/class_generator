<?php

/**
* La classe (lib) CI controller Models_generator est conçue pour faciliter
* la conception d'une chaine représentenant une classe PHP
* afin de copier celle-ci dans un fichier ou de laisser la classe
* sauvegarder le fichier au bon vous semble
*
* @example https://my-site-rec.com/models_generator/generate/my_dbname/my_tbname
*
* @author aguillaume <a.guillaume@highconnexion.com>
* @author achenevat <a.chenevat@highconnexion.com>
* @author fbrunet <f.brunet@highconnexion.com>
* @author qlebian <q.lebian@highconnexion.com>
*
* @version 2.0.0
*
* @see hcnxigniter::Models_generator
*/

class Models_generator_doc_fr extends CI_Controller
{
  /**
  * nom de la base de donnée, reçue en
  * premier paramètre d'un segment URI
  *
  * @see generate_all()
  */
  public $dbname = NULL;


  /**
  * nom de la table à générer, reçue en
  * deuxième paramètre d'un segment URI
  *
  * @see generate_all()
  */
  public $tbname = NULL;

  /**
  * extension de la classe à générer
  *
  * @see format()
  */
  public $extends = 'extends bdd';

  /**
  * implementation de la classe a générer
  *
  * @see format()
  */
  public $implements = '';

  /**
  * rendu de la classe générée formaté en HTML
  *
  * @see format()
  */
  public $result_html = NULL;

  /**
  * rendu de la classe générée formaté pour le fichier PHP
  *
  * @see format()
  */
  public $result_php = NULL;

  /**
  * préfixe par défaut du nom des attributs stockant le résultat
  *
  * @see result_html
  * @see result_php
  * @see format()
  */
  protected $result_prefix = 'result_';

  /**
  * nom des attributs des classes stockant le type de résultat, sans préfixe,
  * afin de sécuriser le protocole de population de ceux-ci
  *
  * @see format()
  */
  protected $allowed_results_types = array('php', 'html');

  /**
  * délimiteur afin de faciliter la génération par type de résultat
  *
  * @see format()
  */
  protected $results_types_delimiters = array('html' => "<br/>", 'php' => "\n");

  /**
  * méthodes supplémentaires à appeler avant, pendant et après
  * la génération de la classe
  *
  * généralement utilisé pour ajouter du contenu sans modifier
  * l'algorithme principal
  *
  * @see format()
  * @see routines()
  */
  protected $routines =
              array(
                'before' => array(),
                'while' => array('generate_crud', 'generate_accessor'),
                'after' => array('format_html')
              );


  /**
  * chemain de destination des classes PHP générées à sauvegarder
  *
  * Note : une façon de ne pas sauvegarder le résultat est de mettre
  *        cet attribut à NULL
  *
  * @see save()
  */
  protected $save_path = '../libraries/bdd/';

  /**
  * attributs relatifs à la classe à générer
  *
  * @see format()
  */
  protected $primary = NULL;
  protected $prefix = NULL;
  protected $fields = NULL;
  protected $fields_str = NULL;
  protected $fields_null = NULL;



  /* Déclaration des Méthodes */



  /**
   * @load_base_frame(no_menu)
   * @access_restrict()
   */
  public function index()
  {
      echo 'ok';
  }

  /**
  * méthode principale, public, afin de générer une représentation
  * de toutes les tables SQL de la base de donnée en classes PHP
  *
  * @param string db
  *                 nom de la base de donnée à utiliser
  *
  * @return void
  *
  * @see generate()
  */
  public function generate_all($db = NULL)
  {
    if (is_null($db))
    {
      //$db = config_item('database');
      $db = 'grattofolies';
    }

    // get all tables name
    $sql = "SELECT table_name AS 'name' FROM information_schema.tables where table_schema='$db'";
    $tables = $this->db->query($sql)->result();

    if (!empty($tables))
    {
      foreach ($tables as $table)
      {
        $this->generate($db, $table->name);
      }
    }
  }

  /**
  * méthode principale, public, afin de générer la représentation
  * d'une ou toutes les tables SQL de la base de donnée en classe PHP,
  * en fonction du paramètre table
  *
  * @param string db
  *                 nom de la base de donnée à utiliser
  * @param string table
  *                 nom de la table à utiliser
  *
  * @return void
  *
  * @see dbname
  * @see tbname
  * @see generate_all()
  * @see build()
  * @see result()
  * @see save()
  */
  public function generate($db = NULL, $table = NULL)
  {
    if (is_null($db))
    {
      //$db = config_item('database');
      $db = 'grattofolies';
    }

    if (is_null($table))
    {
      $this->generate_all($db);
    }
    else
    {
      // set db config
      $this->dbname = $db;
      $this->tbname = $table;

      // get related db table infos
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

  /**
  * méthode populant les attributs souhaités de la classe à générer
  *
  * @param array result
  *                 résultat de la requête SQL décrivant la table courante
  *
  * @return Models_generator
  *
  * @see format()
  */
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

    // format as a string the following allowed results type
    $this->format();

    return $this;
  }

  /**
  * méthode générant les différents types de résultats attendus
  *
  * @return Models_generator
  *
  * @see routines()
  */
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
      // build current result attribut name
      $attr = $this->result_prefix . $type;

      if (property_exists($this, $attr) && !empty($delimiters[$type]))
      {
        // get delimiter following result type
        $delim = $delimiters[$type];

        /* generate PHP class */

        $output[] = '<?php' . $delim . 'class ' . $this->tbname ." $extends $implements {" . $delim;
        $output[] = $delim . "\tvar $" . implode(";$delim\tvar $", array_keys($this->fields)) . ';' . $delim;
        $output[] = $delim . "\t" . 'var $table_prefix = \'' . $this->prefix . '\';';
        $output[] = $delim . "\t" . 'var $_table = \'' . str_replace($this->prefix, '', $this->tbname) . '\';';
        $output[] = $delim . "\t" . 'var $_primary_key = \'' . $this->primary . '\';';
        $output[] = $delim . "\t" . 'var $_champs = array(' . implode(', ', $this->fields_str) . ');';
        $output[] = $delim . "\t" . 'var $_null = array(\'' . implode('\', \'', $this->fields_null) . '\');' . $delim;
        $output[] = $this->routines('while', array($delim, $type), false, " ");
        $output[] = $delim . '}';

        /* end of building */

        //set current result attribute
        $this->{$attr} = implode('', $output);
      }
    }

    // post generation
    $this->routines('after');

    return $this;
  }

  /**
  * méthode permettant la gestion sécurisée d'appel des routines
  * supplémentaires gérant la génération de la classe PHP
  *
  * @param string type
  *                 nom de l'index du tableau contenant les méthodes à appeler
  * @param array params
  *                 paramètres à envoyer aux méthodes à appeler
  * @param bool valid
  *                 valider ou non le statut de chaque retour des méthodes
  * @param mixed implode
  *                 séparateur lors de la conversion du tableau de résultat
  *
  * @return bool statut global des routines appelées
  *
  * @see routines
  * @see format()
  */
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

  /**
  * méthode gérant la sauvegarde de la classe générée dans un fichier
  *
  * @return bool suivant l'existance des fichier et de file_put_contents
  *
  * @see save_path
  * @see tbname
  * @see result()
  */
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
          // rename new file name
          $filename = 'new_' . $filename;
        }

        // save PHP class to file
        return file_put_contents($path.$filename, $this->result('php'));
      }
    }

    return FALSE;
  }

  /**
  * méthode retournant le type de résultat souhaité
  *
  * @param string type
  *           type, sans préfixe, de l'attribut contentant le résultat
  *
  * @return string classe PHP générée, en fonction du type de résultat
  *
  * @see result_html
  * @see result_php
  */
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
  *                  Custom Methods Callable Before, While and After Generation              *
  *                                                                                          *
  *******************************************************************************************/

  /**
  * before building format()
  */

  /**
  * while building format()
  */

  /**
  * méthode supplémentaire permettant création et l'intégration de méthodes
  * dans la classe PHP à générer
  *
  * @param string delim
  *         délimiteur pour le rendu
  * @param string type
  *         type de routine (after / while / before)
  *
  * @return string méthodes de la classe PHP générées
  *
  * @see routines
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

  /**
  * méthode supplémentaire permettant création et l'intégration de méthodes
  * au format CRUD dans la classe PHP à générer
  *
  * @param string delim
  *         délimiteur pour le rendu
  * @param string type
  *         type de routine (after / while / before)
  *
  * @return string méthodes de la classe PHP générées
  *
  * @see routines
  */
  protected function generate_crud($delim, $type)
  {
    $output = array();

    $output = implode('', $output);
    return $output;
  }

  /**
  * méthode supplémentaire permettant création et l'intégration de méthodes
  * au format CRUD dans la classe PHP à générer
  *
  * @param string delim
  *         délimiteur pour le rendu
  * @param string type
  *         type de routine (after / while / before)
  *
  * @return string méthodes de la classe PHP générées
  *
  * @see routines
  */
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

  /**
  * méthode supplémentaire utilisée après la génération
  * de la classe PHP courante afin de concaténer une chaine après le rendu HTML
  *
  * @return bool méthodes de la classe PHP générées
  *
  * @see result_html
  * @see routines
  */
  protected function format_html()
  {
    $this->result_html .= "<br/><br/>";
    return true;
  }
}
