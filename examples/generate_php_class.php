<?php

require_once(__DIR__.'/../src/class/class_builder.php');
require_once(__DIR__.'/../src/class/class_drivers.php');

// generator

$class = new class_generator('MyClass', 'MyParent', 'MyAbstract');

$class->property()->add('$name', 'NULL');
$class->property()->add('$value', 'NULL');

$class->method()->add('__construct', array('params' => '$name = NULL', 'body' => '$this->name = $name;'));
$class->method()->add('get_name', array('params' => '$name', 'body' => 'return $this->name;'));
$class->method()->add('get_value', array('params' => '$value', 'body' => 'return $this->value;'));

//$class->load();

//echo $class->result();


// generator drivers

$generator = class_drivers::factory();

//print_r($generator->php);


// builder

//$builder = new class_builder();
$builder = new class_builder($generator->php);
//$builder = new class_builder($generator->json);

// $builder
//         ->name('qlebian_php')
//         ->extend('hcnx')
//         ->implement('highco')
//         ->property($class->property())
//         ->method($class->method());
//         ->load();

$my_class = class_generator::factory($class);
$your_class = class_generator::factory($class);
$their_class = array('name' => 'TheirClass');
$your_class->name = 'YourClass';

$classes = array(&$my_class, &$your_class, &$their_class);

$builder->load($classes);

echo $builder->output();

foreach ($classes as $class)
{
  $class->save();
}

//$builder->generator()->save();
