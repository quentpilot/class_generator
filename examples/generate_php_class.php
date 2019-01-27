<?php

require_once(__DIR__.'/../src/class/class_builder.php');

// generator

$class = new class_generator('MyClass', 'MyParent', 'MyModel');

$class->property()->add('$name', 'NULL');
$class->property()->add('$value', 'NULL');

$class->method()->add('__construct', array('params' => '$name = NULL', 'body' => '$this->name = $name;'));
$class->method()->add('get_name', array('params' => '$name', 'body' => 'return $this->name;'));
$class->method()->add('get_value', array('params' => '$name', 'body' => 'return $this->name;'));
//$class->load();

//echo $class->result();


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

echo $builder->output();

$builder->generator()->save();
