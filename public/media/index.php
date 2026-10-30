<?php

/*
| /media is both the brief's Media Gallery URL and the public/media asset folder, so web servers answer the bare
| /media request from the folder and never reach Laravel. Servers load this directory index instead; it hands the
| request to the front controller as if it had been routed there (script path reset so the base URL stays "/").
*/
$_SERVER['SCRIPT_FILENAME'] = dirname(__DIR__).'/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

require dirname(__DIR__).'/index.php';
