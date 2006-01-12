<?php
/**
 * Medick generator.
 * $Id$
 */
 
$type= isset($argv[1]) ? $argv[1] : exit("Use one of controller or model for generator.\n");
$name= isset($argv[2]) ? strtolower($argv[2]) : exit($argv[0] . " needs a " . $argv[1] . " name.\n");

switch ($type) {
    case "controller":
        generate_controller($name, "${APP_PATH}");
        break;
    case "model":
        generate_model($name, "${APP_PATH}");
         break;
     default:
         exit("Use one of controller or model for generator.\n");
  }

exit ("\nMedick (\$v: ${MEDICK_V}) [ DONE ].\n");

function generate_controller($name, $app_location) {
    $controller_class_name= ucfirst(strtolower($name)) . 'Controller';
    echo "Creating controller class $controller_class_name from $name\n";
    $methods= array_slice($_SERVER['argv'], 3);

    $controller_class_text="<?php\nclass $controller_class_name extends ApplicationController {\n";

    $views=$app_location.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR;
    if (!is_dir($views.$name)) {
        mkdir($views. $name);
        echo "\tcreate " . $views.$name . "\n";
    } else {
        echo "\texists " . $views.$name . "\n";
    }

    if (!file_exists($views.'layouts'.DIRECTORY_SEPARATOR.$name.'.phtml')) {
    $layout =<<<LAYOUT
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
     <head>
         <title>$name</title>
         <base href="<?=\$__server;?><?=\$__base;?>/" />
         <link rel="stylesheet" href="stylesheet/medick.css" />
         <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
     </head>
     <body><?=\$content_for_layout;?></body>
</html>
LAYOUT;
        file_put_contents($views.'layouts'.DIRECTORY_SEPARATOR.$name.'.phtml',$layout);
        echo "\tcreate ".$views."layouts".DIRECTORY_SEPARATOR.$name.".phtml\n";
    } else {
        echo "\texists ".$views."layouts".DIRECTORY_SEPARATOR.$name.".phtml\n";
    }

    $helper  = $app_location.DIRECTORY_SEPARATOR.'app';
    $helper .= DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.$name.'_helper.php';
    if (!file_exists($helper)) {
        touch($helper);
        echo "\tcreate $helper\n";
    } else {
        echo "\texists $helper\n";
    }

    $forbidden_method_names= array('__construct', '__destruct');
    if (count($methods)) {
         foreach ($methods as $method) {
             if (in_array($method,$forbidden_method_names)) {
                 echo "Skipping method, $method is a forbidden name\n";
                 continue;
             }
             echo "\tadding method: $method \n";
             $controller_class_text .= "\t\tpublic function $method() {    }\n\n";
             $view_location = $views.$name.DIRECTORY_SEPARATOR.$method.'.phtml';
             if (file_exists($view_location)) {
                 echo "\texists $view_location\n";
                 continue;
             }
             $view_text     = "$controller_class_name#$method";
             file_put_contents($view_location, $view_text);
             echo "\tcreate: $view_location\n";
         }
     }
     $controller_class_text.= "\n}\n";
     $controller_location =
         $app_location.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.$name.'_controller.php';
     if (file_exists($controller_location)) {
         echo "\toverwrote ";
     } else {
         echo "\tcreate ";
     }
     file_put_contents($controller_location, $controller_class_text);
     echo "$controller_location\n";
  }

 function generate_model($name, $app_location) {
     $model_class_name = ucfirst(strtolower($name));
     $model_location   = $app_location.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.$name.'.php';
     echo "Generating model $model_class_name from $name\n";
     if (file_exists($model_location)) {
         echo "\texits $model_location\n";
         return;
     }
     $model_class_text =<<<EOCLASS
<?php

 /**
  * This class is part of ${APP_NAME} project
  *
  * @package ${APP_NAME}.models
  * \$Id$
  */
class $model_class_name extends ActiveRecordBase {

     /**
      * Finds a $model_class_name
      *
      * @see ActiveRecordBase::__find()
      * @return mixed
      */
     public static function find() {
         ActiveRecordBase::initialize(__CLASS__);
         return ActiveRecordBase::__find(func_get_args());
     }

  }

EOCLASS;

    file_put_contents($model_location, $model_class_text);
    echo "\tcreate $model_location\n";
  }

 ?>
