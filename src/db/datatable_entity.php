<?php

interface idatatable_entity
{

}

class datatable_entity implements idatatable_entity
{
  public function __construct()
  {

  }

  // protected function orm_attributes()
  // {
  //   $configs = $this->configs;
  //   $prim = '';
  //   $prefix = '';
  //   $fields = array();
  //   $fields_str = array();
  //   $fields_null = array();
  //   $filedata = '';
  //
  //   // get datatable prefix
  //   if (substr($this->datatable, 3, 1) == "_")
  //   {
  //     $prefix = substr($this->datatable, 0, 4);
  //   }
  //
  //   if (!empty($configs))
  //   {
  //     // loop for each table field
  //     for ($i = 0 ; $i < count($configs) ; $i++)
  //     {
  //       // remove prefix
  //       $field = str_replace($prefix, '', $configs[$i]['Field']);
  //       $type = str_replace($prefix, '', $configs[$i]['Type']);
  //
  //       // get primary key
  //       if ($configs[$i]['Key'] == 'PRI')
  //       {
  //         $prim = $field;
  //       }
  //
  //       // format an array of fields name and related type
  //       $fields[$field] = $type;
  //       $fields_str[] = "'" . $field . " ' => '" . $type. "'";
  //
  //       // build an array of nullable fields name
  //       if ($configs[$i]['Null'] == 'YES')
  //       {
  //         $fields_null[] = $field;
  //       }
  //     }
  //   }
  //
  //   $this->attributes->add('primary', $prim)
  //                    ->add('prefix', $prefix)
  //                    ->add('fields', $fields)
  //                    ->add('fields_str', $fields_str)
  //                    ->add('fields_null', $fields_null);
  //
  //
  //   return $this->format_orm_attributes();
  // }
  //
  // protected function format_orm_attributes()
  // {
  //   // pre generation
  //   $this->routines('before');
  //
  //   $extends = $thisextends;
  //   $implements = $this->implements;
  //   $allowed = $this->allowed_results_types;
  //   $delimiters = $this->results_types_delimiters;
  //   $fields = $this->attributes()->get('fields');
  //   $fields_str = $this->attributes()->get('fields_str');
  //   $fields_null = $this->attributes()->get('fields_null');
  //
  //   // loop for each allowed type to set related result
  //   foreach ($allowed as $it => $type)
  //   {
  //     $output = array();
  //     $attr = $this->result_prefix . $type;
  //
  //     if (property_exists($this, $attr) && !empty($delimiters[$type]))
  //     {
  //       // get delimiter following result type
  //       $delim = $delimiters[$type];
  //
  //       // build PHP class
  //       $output[] = '<?php' . $delim . 'class ' . $this->datatable ." $extends $implements {" . $delim;
  //       $output[] = $delim . "\tvar $" . implode(";$delim\tvar $", array_keys($fields)) . ';' . $delim;
  //       $output[] = $delim . "\t" . 'var $table_prefix = \'' . $prefix . '\';';
  //       $output[] = $delim . "\t" . 'var $_table = \'' . str_replace($prefix, '', $this->datatable) . '\';';
  //       $output[] = $delim . "\t" . 'var $_primary_key = \'' . $primary . '\';';
  //       $output[] = $delim . "\t" . 'var $_champs = array(' . implode(', ', $fields_str) . ');';
  //       $output[] = $delim . "\t" . 'var $_null = array(\'' . implode('\', \'', $fields_null) . '\');' . $delim;
  //       $output[] = $this->routines('while', array($delim, $type), false, " ");
  //       $output[] = $delim . '}';
  //
  //       //set current result attribute
  //       $this->{$attr} = implode('', $output);
  //     }
  //   }
  //
  //   // post generation
  //   $this->routines('after');
  //
  //   return $this;
  // }
}
