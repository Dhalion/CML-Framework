<?php 
/**
 * Executes a function from the CML\Classes\Functions namespace using the provided function name and parameters.
 *
 * @param string $functionName The name of the function to execute.
 * @param mixed  ...$parameters The parameters to pass to the function.
 * @return mixed The result of the executed function.
 */
function useTrait(string $functionName, ...$parameters) {
    $class = new class {
        use CML\Classes\Functions\Functions;
        use CML\Classes\Functions\Session;
    };
    return $class->$functionName(...$parameters);
}

/**
 * Adds a function to the list of AJAX functions.
 *
 * This function allows you to register one or more functions to be called via AJAX.
 * The registered functions will be stored in the global variable $cml_ajax_functions.
 */
$cml_ajax_functions = array();
function ajax(...$function) {
    global $cml_ajax_functions;
    $cml_ajax_functions = array_merge($cml_ajax_functions, $function);
}


/**
 * Retrieves or sets the CML configuration values.
 *
 * @param mixed $config (optional) The configuration key or an array of key-value pairs to set.
 * @return mixed The CML configuration values if no parameter is provided, or the value of the specified configuration key.
 */
$cml_config = get_defined_constants(true)['user'];
function cml_config($config = null){
    global $cml_config;

    if(is_null($config)){
        return $cml_config;
    }

    if(is_string($config)){
        return isset($cml_config[$config]) ? $cml_config[$config] : null;
    }

    if(is_array($config)){
        foreach($config as $key => $value){
            $cml_config[$key] = $value;
            if(!defined($key)){
                define($key, $value);
            }
        }
    }
}
?>