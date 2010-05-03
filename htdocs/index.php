<?php

// MICROSITE LOADER

// Do not change this line, it indicates where the base filestructure starts
define('MICROSITE_PATH', dirname(__FILE__));

// Change this line if your config file is in a different directory
define('MICROSITE_CONFIG', MICROSITE_PATH . '/microsite/microsite_config.ini');

// Change this line to where the microsite.php file is located, if in a different directory
include 'microsite/index.php';

?>