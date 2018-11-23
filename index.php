<?php
//Front controller for societyleadership


//load some configuration settings
global $ini_array;
$ini_array = parse_ini_file(__DIR__ . '/society_leadership_config.ini', true);

//load libraries
require(__DIR__ . '/Validator.php');
require(__DIR__ . '/society_lib.php');

// Send output to browser
\SocietyLeadership\render_view();
exit(0);