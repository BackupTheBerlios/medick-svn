<?php
// $Id$
$medick_core    = dirname(dirname(__FILE__));
$medick_version = str_replace("\n","",file_get_contents($medick_core.DIRECTORY_SEPARATOR.'VERSION'));

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
        echo "Overwrite [Y/N]: ";
        $answer= trim(fgets(STDIN));
        if (in_array(strtoupper($answer), array('Y','YES'))) {
            if(@file_put_contents($to, $contents)) {
                echo "\toverwrote {$to}\n";
            } else {
                exit("Cannot overwrite {$to}, permissions?\n");
            }
        }
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

function copy_files($from_folder, $to_folder) {

    if (!is_dir($from_folder)) {
        exit("Cannot copy from {$from_folder}, no such file or directory\n");
    }
    if (!is_dir($to_folder)) {
        exit("Cannot copy to {$to_folder}, no such file or directory\n");
    }

    $d = dir($from_folder);
    while (false !== ($entry = $d->read())) {
        if($entry!='.' && $entry!='..') {
            if (is_dir($entry)) continue; // skip folders.
            $from_file = $from_folder . DIRECTORY_SEPARATOR . $entry;
            $to_file   = $to_folder . DIRECTORY_SEPARATOR . $entry;
            if (is_file($to_file)) {
                echo "\texists {$to_file}\n";
                continue;
            }
            if (!copy($from_file, $to_file)) {
                echo "cannot copy: {$from_file} to {$to_file}!\n";
                continue;
            } else {
                echo "\tcreate {$to_file}\n";
            }
        }
    }
}


$app_name= isset($argv[1]) ? $argv[1] : exit("No Application Location Specified.\n");
$x = explode(DIRECTORY_SEPARATOR,$app_name); $short_name = end($x);
$app_location= getcwd() . DIRECTORY_SEPARATOR . $app_name;
echo "Creating application: $short_name\n";
echo "Location: $app_location\n\n";
mk_dir($app_location);

$folders= array(
    'app'         =>'app',
    'models'      =>'app' . DIRECTORY_SEPARATOR . 'models',
    'controllers' =>'app' . DIRECTORY_SEPARATOR . 'controllers',
    'helpers'     =>'app' . DIRECTORY_SEPARATOR . 'helpers',
    'views'       =>'app' . DIRECTORY_SEPARATOR . 'views',
    'layouts'     =>'app' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'layouts',
    'conf'        =>'conf',
    'scripts'     =>'scripts',
    'db'          =>'db',
    'libs'        =>'libs',
    'doc'         =>'doc',
    'log'         =>'log',
    'public'      =>'public',
    'js'          =>'public' . DIRECTORY_SEPARATOR . 'javascript',
    'css'         =>'public' . DIRECTORY_SEPARATOR . 'stylesheet',
    'img'         =>'public' . DIRECTORY_SEPARATOR . 'images'
  );

foreach ($folders as $folder) {
    mk_dir($app_location . DIRECTORY_SEPARATOR . $folder);
}

copy_files($medick_core. DIRECTORY_SEPARATOR .
            'skel'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'javascript'.DIRECTORY_SEPARATOR,
            $app_location.DIRECTORY_SEPARATOR.$folders['js']);
            
$files= array(
  'skel' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php'
                              => 'public' . DIRECTORY_SEPARATOR . 'index.php',
  'skel' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'default.htaccess'
                              => 'public' . DIRECTORY_SEPARATOR . '.htaccess',
  'skel' . DIRECTORY_SEPARATOR . 'scripts'. DIRECTORY_SEPARATOR . 'generator.php'
                              => 'scripts'. DIRECTORY_SEPARATOR . 'generator.php',
  'skel' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'application.xml'
                              => 'conf'   . DIRECTORY_SEPARATOR . $short_name.'.xml',
  'skel' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.ini'
                              => 'conf'   . DIRECTORY_SEPARATOR . 'database.ini',
  'skel' . DIRECTORY_SEPARATOR . 'db'     . DIRECTORY_SEPARATOR . 'schema.sql'
                              => 'db'     . DIRECTORY_SEPARATOR . 'schema.sql',
  'skel' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.html'
                              => 'public' . DIRECTORY_SEPARATOR . 'index.html',
  'skel' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'stylesheet' . DIRECTORY_SEPARATOR . 'medick.css'
                              => 'public' . DIRECTORY_SEPARATOR . 'stylesheet' . DIRECTORY_SEPARATOR . $short_name . '.css',
  'skel' . DIRECTORY_SEPARATOR . 'app'    . DIRECTORY_SEPARATOR . 'app_controller.php'
                              => 'app'    . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'application.php',
  'skel' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'application.routes.php'
                              => 'conf'   . DIRECTORY_SEPARATOR . $short_name.'.routes.php'
);

$search= array(
            '${medick.core}',
            '${app.path}',
            '${app.name}',
            '${ds}',
            '${medick.version}',
            '${date}'
          );

$replace= array(
            $medick_core,
            $app_location,
            $short_name,
            DIRECTORY_SEPARATOR,
            $medick_version,
            date('Y M d H:i:s')
          );

foreach ($files as $from=>$file) {
    $contents= str_replace($search, $replace, file_get_contents($medick_core.DIRECTORY_SEPARATOR.$from));
    list($to,$orig)= explode(DIRECTORY_SEPARATOR, $file,2);
    write_file($contents, $app_location.DIRECTORY_SEPARATOR.$folders[$to].DIRECTORY_SEPARATOR.$orig);
}

// plain file
write_file(' ',$app_location.DIRECTORY_SEPARATOR.$folders['log'].DIRECTORY_SEPARATOR.$short_name.'.log',0777);

echo "\nMedick (\$v: $medick_version) [ DONE ].\n";

?>
