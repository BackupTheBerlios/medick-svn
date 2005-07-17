<?php
// {{{ License
//////////////////////////////////////////////////////////////////////////////////
//
// Copyright (c) 2005 Oancea Aurelian <aurelian@locknet.ro>
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are met:
//
//   * Redistributions of source code must retain the above copyright notice, 
//   this list of conditions and the following disclaimer. 
//   * Redistributions in binary form must reproduce the above copyright notice,
//   this list of conditions and the following disclaimer in the documentation 
//   and/or other materials provided with the distribution. 
//   * Neither the name of locknet.ro nor the names of its contributors may 
//   be used to endorse or promote products derived from this software without 
//   specific prior written permission.
//
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
// AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
// IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
// DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE
// FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
// DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
// SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
// CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
// OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
// OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
// 
// $Id$
// 
//////////////////////////////////////////////////////////////////////////////////
// }}}

exec('rm -rf app/');

error_reporting(E_ALL);

// main TOP_LOCATION.
define('TOP_LOCATION', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
set_include_path( TOP_LOCATION . 'libs'   . DIRECTORY_SEPARATOR . PATH_SEPARATOR . 
                  TOP_LOCATION . 'vendor' . DIRECTORY_SEPARATOR . PATH_SEPARATOR . dirname(__FILE__)
                );
                
define('VERBOSE', FALSE);

// {{{ integrity chck
include_once('targets/integrity.php');
// }}}

include_once('configurator/Configurator.php');
$config = Configurator::factory('XML', TOP_LOCATION . 'config' . DIRECTORY_SEPARATOR . 'application.xml');

print_header('Scaffolding Todo Model');
include_once('creole/Creole.php');
include_once('active/support/Inflector.php');
try {
    if(VERBOSE) echo "Establish a DB Connection";
    $conn = Creole::getConnection($config->getDatabaseDsn());
} catch (SQLException $sqlEx) {
    echo "..........[ FAILED ]\n";
    done ($sqlEx->getMessage, 255);
}
if(VERBOSE) echo "..........[ OK ]\n";


$model = 'todo';
$Umodel = ucfirst($model);
$models = Inflector::pluralize($model);
$table_info = $conn->getDatabaseInfo()->getTable($models);
$pk = $table_info->getPrimaryKey()->getName();
$date = strftime("%d/%m/%Y %H:%M:%S", time());
$pattern = '/^(.*)_id$/';

$startscript="scripts/generator.php";
$fileowneruid=fileowner($startscript);
$fileownerarray=posix_getpwuid($fileowneruid);
$user=$fileownerarray['name'];

$app_php = $config->getProperty('application_path') . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'application.php';
echo " -- creating ApplicationController: " . $app_php;
if (is_file($app_php)) {
    echo ".....[ FILE EXISTS - SKIPING ]\n";
} else {
    if (!is_dir($config->getProperty('application_path'))) {
        generate_application_skeleton($config->getProperty('application_path'));
    }
    $_app = file_get_contents('scripts/templates/application.txt');
    $s1 = str_replace('${__USER__}', $user, $_app);
    $s2 = str_replace('${__DATE__}', $date, $s1);
    file_put_contents($app_php, $s2);
    echo ".....[ OK ]\n";
}

$file = $config->getProperty('application_path') . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . $model . '.php';
echo " -- creating " . $Umodel . " Model: " . $file;
if (is_file($file)) {
    echo ".....[ FILE EXISTS - SKIPING ]\n";
} else {
    $_app = file_get_contents('scripts/templates/model.txt');
    $s1 = str_replace('${__USER__}', $user, $_app);
    $s2 = str_replace('${__DATE__}', $date, $s1);
    $s3 = str_replace('${Model}', $Umodel, $s2);
    file_put_contents($file, $s3);
    echo ".....[ OK ]\n";
}

$file = $config->getProperty('application_path') . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . $model . '_helper.php';
echo " -- creating " . $model . " Helper: " . $file;
if (is_file($file)) {
    echo ".....[ FILE EXISTS - SKIPING ]\n";
} else {
    $_app = file_get_contents('scripts/templates/helper.txt');
    $s1 = str_replace('${__USER__}', $user, $_app);
    $s2 = str_replace('${__DATE__}', $date, $s1);
    $s3 = str_replace('${model}', $model, $s2);
    file_put_contents($file, $s3);
    echo ".....[ OK ]\n";
}

$file = $config->getProperty('application_path') . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $model . '_controller.php';
echo " -- creating " . $model . " Controller: " . $file;
if (is_file($file)) {
    echo ".....[ FILE EXISTS - SKIPING ]\n";
} else {
    $_app = file_get_contents('scripts/templates/controller.txt');
    $s1 = str_replace('${__USER__}', $user, $_app);
    $s2 = str_replace('${__DATE__}', $date, $s1);
    
    $buff = '';
    foreach( $table_info->getColumns() as $col) {
        if ($col->getName() == $pk) continue;
        if ( preg_match($pattern, $col->getName(), $matches) ) continue;
        $buff .= "\$${model}->" . $col->getName() . " = \$this->params['" . $col->getName() . "'];\n\t\t";
    }
    
    $s21 = str_replace('${__FIELDS__}', $buff, $s2);
    
    $s22 = str_replace('${__PK__}', $pk, $s21);

    $s3 = str_replace('${model}', $model, $s22);
    $s4 = str_replace('${Model}', $Umodel, $s3);
    $s5 = str_replace('${models}', $models, $s4);
    file_put_contents($file, $s5);
    echo ".....[ OK ]\n";
}

$views_dir = $config->getProperty('application_path') . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $model;
if (!is_dir($views_dir)) {
    mkdir($views_dir, 0755);
}

// ------------------------- ALL
$file = $views_dir . DIRECTORY_SEPARATOR . 'all.phtml';
echo " -- creating all " . $model . " view: " . $file;
if (is_file($file)) {
    echo ".....[ FILE EXISTS - SKIPING ]\n";
} else {
    $_app = file_get_contents('scripts/templates/all.txt');
    $s1 = str_replace('${__USER__}', $user, $_app);
    $s2 = str_replace('${__DATE__}', $date, $s1);

    $buff = '<br />';

    foreach( $table_info->getColumns() as $col) {
        if ($col->getName() == $pk) continue;       
        if ( preg_match($pattern, $col->getName(), $matches) ) continue;
        $buff .= ucfirst($col->getName()) . ": <?=\$${model}->" . $col->getName() . ";?><br />";
    }
    
    // $buff = substr($buff, 0, -4) . "<br />";
    
    $s21 = str_replace('${__BUFFER__}', $buff, $s2);
    
    $s3 = str_replace('${model}', $model, $s21);
    $s4 = str_replace('${Model}', $Umodel, $s3);
    $s5 = str_replace('${models}', $models, $s4);
    $s6 = str_replace('${__PK__}', $pk, $s5);
    
    file_put_contents($file, $s6);
    echo ".....[ OK ]\n";
}

// ------------------------- ANEW
$file = $views_dir . DIRECTORY_SEPARATOR . 'anew.phtml';
echo " -- creating all " . $model . " view: " . $file;
if (is_file($file)) {
    echo ".....[ FILE EXISTS - SKIPING ]\n";
} else {
    $_app = file_get_contents('scripts/templates/anew.txt');
    $s1 = str_replace('${__USER__}', $user, $_app);
    $s2 = str_replace('${__DATE__}', $date, $s1);

    $buff = '<br />';
    
    foreach( $table_info->getColumns() as $col) {
        if ($col->getName() == $pk) continue;
        if ( preg_match($pattern, $col->getName(), $matches) ) continue;
        $name = $col->getName();
        $size = $col->getSize();
        $type = CreoleTypes::getCreoleName($col->getType());
        switch ($type) {
            case 'INT':
            case 'INTEGER':
            case 'VARCHAR':
            default:
                $buff .= ucfirst($name) . 
                    ': <?= Form::text(\'' . $name . '\', NULL, array(\'size\'=>' . $size . ')); ?><br />';
                break;
        }
    }
    
    $s21 = str_replace('${__BUFFER__}', $buff, $s2);
    
    $s3 = str_replace('${model}', $model, $s21);
    $s4 = str_replace('${Model}', $Umodel, $s3);
    $s5 = str_replace('${models}', $models, $s4);
    
    
    file_put_contents($file, $s5);
    echo ".....[ OK ]\n";
}


// ------------------------- EDIT
$file = $views_dir . DIRECTORY_SEPARATOR . 'edit.phtml';
echo " -- creating edit form for " . $model . " : " . $file;
if (is_file($file)) {
    echo ".....[ FILE EXISTS - SKIPING ]\n";
} else {
    $_app = file_get_contents('scripts/templates/edit.txt');
    $s1 = str_replace('${__USER__}', $user, $_app);
    $s2 = str_replace('${__DATE__}', $date, $s1);

    $buff = '<br />';
    
    foreach( $table_info->getColumns() as $col) {
        if ($col->getName() == $pk) continue;

        if ( preg_match($pattern, $col->getName(), $matches) ) continue;
        $name = $col->getName();
        $size = $col->getSize();
        $type = CreoleTypes::getCreoleName($col->getType());
        switch ($type) {
            case 'INT':
            case 'INTEGER':
            case 'VARCHAR':
            default:
                $buff .= ucfirst($name) . 
                    ': <?= Form::text(\'' . $name . '\', $${model}->' . $name . ', array(\'size\'=>' . $size . ')); ?><br />';
                break;
        }
    }
    
    $s21 = str_replace('${__BUFFER__}', $buff, $s2);
    $s22 = str_replace('${__PK__}', $pk, $s21);
    $s3 = str_replace('${model}', $model, $s22);
    $s4 = str_replace('${Model}', $Umodel, $s3);
    $s5 = str_replace('${models}', $models, $s4);
    
    
    file_put_contents($file, $s5);
    echo ".....[ OK ]\n";
}

function generate_application_skeleton($path) {
    $top = $path . DIRECTORY_SEPARATOR;
    mkdir($top . 'controllers', 0755, TRUE);
    mkdir($top . 'helpers',0755);
    mkdir($top . 'models',0755);
    mkdir($top . 'views',0755);
}
function print_header($text) {
    echo "======> $text.\n";
}
function done ($message = '', $error_code = 0) {
    echo $message . "\n";
    exit ($error_code);
}
