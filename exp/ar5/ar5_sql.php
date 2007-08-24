<?php
// $Id$

class SQLException extends MedickException {  }

class ActiveRecordException extends MedickException { }

// xxx.
class SQLType extends Object {

  // sql type to php type
  public static function getPhpType( $type ) {
    if( $type == 'integer' || $type == 'int') return 'Integer';
    else return 'String';
    // elseif( $type == 'varchar' || $type == 'string' || $type == 'text') return 'String';
    // elseif( $type == 'timestamp' || $type == 'time' || $type == 'date') return 'Time';
    // else throw new SQLException('Unknow type: "' . $type . '"');
  }

}

