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

// integrity chck
$handler = fopen('scripts/targets/files.ini', 'r');
print_header('Integrity check');
while (!feof($handler)) {
	$file = trim(fgets($handler));
    if ($file == '') continue;
    // echo $file . " .....";
    if (is_file($file)) {
        // echo "..... [ OK ]\n";
    } else {
        done(".....[ FAILED ]\n" . $file . " is not a file!", 255);
    }
}
fclose($handler);

// include_path, rewrite the existing one
set_include_path( TOP_LOCATION . 'libs'   . DIRECTORY_SEPARATOR . PATH_SEPARATOR . 
                  TOP_LOCATION . 'vendor' . DIRECTORY_SEPARATOR . PATH_SEPARATOR
                );
echo "Setting the path..........[ OK ]\n";
include_once('configurator/Configurator.php');
Configurator::factory('XML', TOP_LOCATION . 'config' . DIRECTORY_SEPARATOR . 'application.xml');
echo "Configurator loaded..........[ OK ]\n";
print_header('Scaffolding Todo Model');
include_once('creole/Creole.php');

try {
    echo "Establish a DB Connection";
    Creole::getConnection(Configurator::getInstance()->getDatabaseDsn());
} catch (SQLException $sqlEx) {
    echo "..........[ FAILED ]\n";
    done ($sqlEx->getMessage, 255);
}
echo "..........[ OK ]\n";






function print_header($text) {
    echo "////////////////////\n" .
         "======> $text.\n" .
         "////////////////////\n";
}

function done ($message = '', $error_code = 0) {
    echo $message . "\n";
    exit ($error_code);
}
