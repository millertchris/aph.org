<?php
/*
 * We are removing all the tagged pages and instead throwing a 404.
 * APH-469
 */


global $wp_query;

$wp_query->set_404();
status_header(404);
include(dirname(__DIR__)) . '/404.php';

