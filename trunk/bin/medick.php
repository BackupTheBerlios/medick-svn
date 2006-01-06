<?php
// $Id$
$medick_core=dirname(dirname(__FILE__));

function mk_dir($dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir)) {
          echo "\tcreate ";
        } else {
          exit("Can create {$dir}, permissions?\n");
        }
    } else {
        echo "\texists ";
    }
    
    echo "{$dir}\n";
}

function write_file($contents, $to, $mode= FALSE) {
    if (file_exists($to)) {
        echo "\texists {$to}\n";
    } else {
        if(@file_put_contents($to, $contents)) {
            echo "\tcreate {$to}\n";
        } else {
            exit("Cannot create {$to}, permissions?\n");
        }
    }
    if ($mode) {
        if (!chmod($to, $mode)) exit("Cannot set permissions to {$mode} on {$to}\n");
    }
}

$folders= array(
    'app'         =>'app',
    'models'      =>'app' . DIRECTORY_SEPARATOR . 'models',
    'controllers' =>'app' . DIRECTORY_SEPARATOR . 'controllers',
    'helpers'     =>'app' . DIRECTORY_SEPARATOR . 'helpers',
    'views'       =>'app' . DIRECTORY_SEPARATOR . 'views',
    'layouts'     =>'app' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'layouts',
    'conf'        =>'conf',
    'db'          =>'db',
    'libs'        =>'libs',
    'doc'         =>'doc',
    'log'         =>'log',
    'public'      =>'public',
    'js'          =>'public' . DIRECTORY_SEPARATOR . 'javascript',
    'css'         =>'public' . DIRECTORY_SEPARATOR . 'stylesheet',
    'img'         =>'public' . DIRECTORY_SEPARATOR . 'images'
  );

$app_name= isset($argv[1]) ? $argv[1] : exit("No Application Location Specified.\n");

$x = explode(DIRECTORY_SEPARATOR,$app_name); $short_name = end($x);

$app_location= getcwd() . DIRECTORY_SEPARATOR . $app_name;

echo "Creating application: $short_name\n";
echo "Location:\n\t$app_location\n\n";

mk_dir($app_location);

foreach ($folders as $folder) {
    mk_dir($app_location . DIRECTORY_SEPARATOR . $folder);
}

$files= array(
  'skel' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php' 
                              => 'public' . DIRECTORY_SEPARATOR . 'index.php',
  'skel' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'default.htaccess'  
                              => 'public' . DIRECTORY_SEPARATOR . '.htaccess',
  'skel' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'application.xml'        
                              => 'conf'   . DIRECTORY_SEPARATOR . $short_name.'.xml',
  'skel' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'application.routes.php' 
                              => 'conf'   . DIRECTORY_SEPARATOR . $short_name.'.routes.php'
);

$search= array(
            '${LOG}',
            '${CORE}',
            '${APP_PATH}',
            '${APP_NAME}'
          );

$replace= array(
            $app_location.DIRECTORY_SEPARATOR.$folders['log'].DIRECTORY_SEPARATOR.$app_name.'.log',
            $medick_core,
            $app_location,
            $app_name
          );

foreach ($files as $from=>$file) {
    $contents= str_replace($search, $replace, file_get_contents($medick_core.DIRECTORY_SEPARATOR.$from));
    list($to,$orig)= explode(DIRECTORY_SEPARATOR, $file,2);
    write_file($contents, $app_location.DIRECTORY_SEPARATOR.$folders[$to].DIRECTORY_SEPARATOR.$orig);
}

// plain files.
write_file(' ',$app_location.DIRECTORY_SEPARATOR.$folders['log'].DIRECTORY_SEPARATOR.$short_name.'.log',0777);

$schema_sql=<<<EOSQL
-- \$Id$
-- Database Schema for $app_name

EOSQL;

$index_html=<<<EOHTML
<html>
  <head>
    <!-- \$Id$ -->
    <title>Welcome to Medick!</title>
  </head>
  <body>
    <h3><center>Welcome To Medick</center></h3>
    <p><b>Application:</b> $short_name</p>
    <p><b>Develop $app_name on : </b>$app_location</p>
    <p>Setup a default controller in <i>$app_location/config/$short_name.routes.php</i>, and remove this file.</p>
    <p>Ask for support on medick <a href="https://lists.berlios.de/mailman/listinfo/medick-devel">development list</a>.</p>
  </body>
</html>

EOHTML;


$application_controller=<<<EOCLASS
<?php

  /**
   * This class is part of $short_name project
   *
   * Methods added here will be available in all your controllers.
   * \$Id$
   * @package $app_name.controllers
   */
  class ApplicationController extends ActionControllerBase {

  }
    
EOCLASS;

write_file($index_html, $app_location.DIRECTORY_SEPARATOR.$folders['public'].DIRECTORY_SEPARATOR.'index.html');

write_file($application_controller, $app_location.DIRECTORY_SEPARATOR.$folders['controllers'].DIRECTORY_SEPARATOR.'application.php');

write_file($schema_sql,$app_location.DIRECTORY_SEPARATOR.$folders['db'].DIRECTORY_SEPARATOR.'schema.sql');

echo "\nmedick (\$v:" . str_replace("\n","",file_get_contents($medick_core.DIRECTORY_SEPARATOR.'VERSION')) . ") done.\n";

