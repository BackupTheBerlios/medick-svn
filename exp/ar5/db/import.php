<?php
// $Id$

$dbh= sqlite_open('db/aymo.sqlite');
sqlite_query( file_get_contents('db/sqlite.schema'), $dbh );
  
