<?php

/**
 * Array to store AJAX functions.
 *
 * @var array
 */
$cml_ajax_functions = array();

/**
 * Array to store CML configuration constants.
 *
 * @var array
 */
$cml_config = get_defined_constants(true)['user'];

/**
 * Counter for the number of database requests made.
 *
 * @var int
 */
$cml_db_request_amount = 0;

/**
 * Array to store database query information.
 *
 * @var array
 */
$cml_db_request_query = array();
