<?php
//
// $Id: Request.php 447 2007-07-23 17:44:43Z aurelian $
//

/**
 * It is an incoming request from the user
 * 
 * Contains various informations about the Request Parameters
 * @see HTTPRequest, Dispatcher, Route
 * @package medick.action.controller
 * @author Aurelian Oancea
 */
class Request extends Object {

    /** @var array
        current request parameters */
    private $params = array();
    
    /** 
     * Hidden Constructor 
     */
     protected function Request() { }

    /**
     * It gets a Request Parameter
     * 
     * @param mixed, param, the paremeter name
     * @return the param value of NULL if this param was not passed with this Resuest
     */
    public function getParameter($param) {
      die('DEPRECATED!!!!');
        return $this->hasParameter($param) ? $this->params[$param] : null;
    }

    public function parameter($name) {
      $args= func_get_args();
      if( sizeof($args) == 1 ) {
        return isset($this->params[$name]) ? $this->params[$name] : null;
      } else {
        $this->params[$name]= $args[1];
      }
    }

    /**
     * Check if the current Request has the parameter with the specified name
     * 
     * @param string param_name the parameter name
     * @return bool TRUE if the parameter_name is included in this request, FALSE otherwise
     */
    public function hasParameter($param_name) {
      die('DEPRECATED!!!');
        return isset($this->params[$param_name]);
    }

    /**
     * It gets all the parameters of this Request
     * 
     * @return array this request parameters.
     */
    public function getParameters() {
      die(__METHOD__ . " --> DEPRECATED!!!");
        return $this->params;
    }

    /**
     * It sets a Request Parameter
     *
     * @param string, name, the name of the param to set
     * @param mixed, value, value of the param
     * @return void
     */
    public function setParameter($name, $value) {
      die(__METHOD__ . " --> DEPRECATED!!!");
        $this->params[$name] = $value;
    }

    /**
     * It adds an array of parameters on this Request
     *
     * @param array parameters, parameters name/value pairs
     * @return void
     */ 
    public function parameters( Array $parameters=array() ) {
        foreach ($parameters as $name=>$value) {
            $this->parameter($name, $value);
        }
    }
    
    /**
     * Gets a string representation of this Object
     *
     * @return string
     */ 
    public function toString() {
        $buff = "{".$this->getClassName()."}-->\n";
        foreach ($this->getParameters() as $name=>$value) {
            $buff .= "[{$name}=";
            if (is_array($value)) {
                $buff .= "\n\t[Array:\n";
                foreach ($value as $k=>$v) {
                    $buff .= "\t\t[{$k}=";
                    if (is_array($v)) {
                        $buff .= "[Array]\n";
                        continue;
                    }
                    if (strlen($v)>75) {
                        $buff .= substr(str_replace("","\n",$v),0,75) ." .....]\n";
                    } else {
                        $buff .= "$v]\n";
                    }
                }
                $buff .= "]]\n";
            } else {
                $buff .= "{$value}]";
            }
        }
        return $buff;
    }
}
