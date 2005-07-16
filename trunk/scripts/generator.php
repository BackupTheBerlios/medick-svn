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

error_reporting(E_ALL);

// main TOP_LOCATION.
define('TOP_LOCATION', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);

define('VERBOSE', FALSE);

// integrity chck
$handler = fopen('scripts/targets/files.ini', 'r');
print_header('Integrity check');
while (!feof($handler)) {
	$file = trim(fgets($handler));
    if ($file == '') continue;
    if(VERBOSE) echo $file . " .....";
    if (is_file($file)) {
        if(VERBOSE) echo "..... [ OK ]\n";
    } else {
        done(".....[ FAILED ]\n" . $file . " is not a file!", 255);
    }
}
fclose($handler);

if(VERBOSE) echo "Setting the path..........[ OK ]\n";

set_include_path( TOP_LOCATION . 'libs'   . DIRECTORY_SEPARATOR . PATH_SEPARATOR . 
                  TOP_LOCATION . 'vendor' . DIRECTORY_SEPARATOR . PATH_SEPARATOR
                );
                
include_once('configurator/Configurator.php');
$config = Configurator::factory('XML', TOP_LOCATION . 'config' . DIRECTORY_SEPARATOR . 'application.xml');

if(VERBOSE) echo "Configurator loaded..........[ OK ]\n";

echo "......[ OK ]\n";

print_header('Scaffolding Todo Model');
include_once('creole/Creole.php');

try {
    if(VERBOSE) echo "Establish a DB Connection";
    Creole::getConnection($config->getDatabaseDsn());
} catch (SQLException $sqlEx) {
    echo "..........[ FAILED ]\n";
    done ($sqlEx->getMessage, 255);
}
if(VERBOSE) echo "..........[ OK ]\n";


$model = 'todo';

$Umodel = ucfirst($model);


$date = strftime("%d/%m/%Y %H:%M:%S", time());

$startscript="scripts/generator.php";
$fileowneruid=fileowner($startscript);
$fileownerarray=posix_getpwuid($fileowneruid);
$user=$fileownerarray['name'];

echo " -- creating ApplicationController.....";
$app_php = $config->getProperty('application_path') . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'application.php';
if (is_file($app_php)) {
    echo ".....[ FILE EXISTS - SKIPING ]\n";
} else {
    if (!is_dir($config->getProperty('application_path'))) {
        generate_application_path($config->getProperty('application_path'));
    }
    $_app = file_get_contents('scripts/templates/application.txt');
    $s1 = str_replace('${__USER__}', $user, $_app);
    $s2 = str_replace('${__DATE__}', $date, $s1);
    file_put_contents($app_php, $s2);
    echo ".....[ OK ]\n";
}

echo " -- creating " . $Umodel . " Model.....";
$file = $config->getProperty('application_path') . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR . $model . '.php';
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

echo " -- creating " . $model . " Helper.....";
$file = $config->getProperty('application_path') . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . $model . '_helper.php';
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

echo " -- creating " . $model . " Controller.....";
$file = $config->getProperty('application_path') . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $model . '_controller.php';
if (is_file($file)) {
    echo ".....[ FILE EXISTS - SKIPING ]\n";
} else {
    $_app = file_get_contents('scripts/templates/controller.txt');
    $s1 = str_replace('${__USER__}', $user, $_app);
    $s2 = str_replace('${__DATE__}', $date, $s1);
    $s3 = str_replace('${model}', $model, $s2);
    $s4 = str_replace('${Model}', $Umodel, $s3);
    file_put_contents($file, $s4);
    echo ".....[ OK ]\n";
}

function generate_application_path($path) {
    $top = $path . DIRECTORY_SEPARATOR;
    if (!mkdir($top . 'controllers', 0755, TRUE)) {
        done(".....[ FAILED ]\nCannot create " . $top . "controllers folder.", 255);
    }
    if (!mkdir($top . 'helpers',0755)) {
        done(".....[ FAILED ]\nCannot create " . $top . "helpers folder.", 255);
    }
    if (!mkdir($top . 'models',0755)) {
        done(".....[ FAILED ]\nCannot create " . $top . "models folder.", 255);
    }
    if (!mkdir($top . 'views',0755)) {
        done(".....[ FAILED ]\nCannot create " . $top . "views folder.", 255);
    }
}

function print_header($text) {
    echo "======> $text.\n";
}

function done ($message = '', $error_code = 0) {
    echo $message . "\n";
    exit ($error_code);
}
