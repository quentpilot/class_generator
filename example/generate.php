<?php

// include main class
require_once(__DIR__.'/../src/class_generator.php');

// define class attributes
$attributes = array(
  '$name' => 'my_class',
  '$type' => 'game'
);

// define class methods
$methods = array(
  'get' => array(
    'params' => array('$property'),
    'body' => array(
      'return $this->{$property};',
    ),
  ),
  'set' => array(
    'params' => array('$property', '$value = NULL'),
    'body' => array(
      '$this->{$property} = $value;',
    ),
  )
);

// instanciate sub generators
$engines = array(
  class_attributes::factory($attributes),
  class_methods::factory($methods),
);

// instanciate class builder
$entity = class_entity::factory(array('gig_game_ig', 'ani_animations'), 'bdd', 'iorm', '', $engines);

// instanciate class builder
$generator = new class_generator($entity);

// run generator
$generator->load();

foreach ($generator->result() as $name => $class)
{
  // print class built
  print_r($class->result('php'));

  // save into file wanted result type
  file_put_contents(__DIR__.'/output/'.$name.'.php', $class->result('php'));
}
