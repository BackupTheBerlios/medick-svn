<?php
/* $Id$ */
function get_comment(Reflector $reflector) {
    $comments= explode("\n", $reflector->getDocComment());
    foreach ($comments as $line) {
        $nameStart = strpos($line, '@desc: ');
        if ( (FALSE === $nameStart) ) {
            continue;
        } else {
            return trim(substr($line, $nameStart + 6));
        }
    }
    return 'No description available!';
}

function get_controller(ReflectionClass $controller) {
    return strtolower(str_replace('Controller','',$controller->getName()));
}

?>