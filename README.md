# class_generator
class_generator is a PHP library used to create one or several PHP class strings or for other languages ready to be saved in a PHP file

# Revisions
Actually, the working version would be the tag '2.0.0-dev', tagged from 'develop' branch.

# Examples from current branch 'develop'
The script '/examples/generate_php_class.php' gives you an example of class_generator first, then class_builder ;
which would help you build string by calling class methods consecutively.


--- following code comes from generate_php_class.php file usage example ---


## class_generator

```
$class = new class_generator('MyClass', 'MyParent', 'MyModel');

$class->property()->add('$name', 'NULL');
$class->property()->add('$value', 'NULL');

$class->method()->add('__construct', array('params' => '$name = NULL', 'body' => '$this->name = $name;'));
$class->method()->add('get_name', array('params' => '$name', 'body' => 'return $this->name;'));
$class->method()->add('get_value', array('params' => '$value', 'body' => 'return $this->value;'));

$class->load();

echo $class->result();
```

## class_builder

```
$builder = new class_builder();

$builder
        ->type("<?php\nclass")
        ->name('MyClass')
        ->extends('MyParent')
        ->implements('MyModel')
        ->property($class->property())
        ->method($class->method())
        ->load();

echo $builder->output();
```
