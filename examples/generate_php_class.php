<?php

require_once(__DIR__.'/../src/class/class_builder.php');
require_once(__DIR__.'/../src/drivers/class_drivers.php');

// generator

$class = new class_generator('MyClass', 'MyParent', 'MyAbstract');

$class->property()->add('$name', 'NULL');
$class->property()->add('$value', 'NULL');

$class->method()->add('__construct', array('params' => '$name = NULL', 'body' => '$this->name = $name;'));
$class->method()->add('get_name', array('params' => '$name', 'body' => 'return $this->name;'));
$class->method()->add('get_value', array('params' => '$value', 'body' => 'return $this->value;'));

$class->load();

//echo $class->result();


// generator drivers

$generator = class_drivers::factory();

print_r($generator->css);


// builder

$builder = new class_builder();

$builder
        ->type("<?php\nclass")
        ->name('qlebian')
        ->extends('hcnx')
        ->implements('highco')
        ->property($class->property())
        ->method($class->method())
        ->load();

//echo $builder->output();

//$builder->generator()->save();
