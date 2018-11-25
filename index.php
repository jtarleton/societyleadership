<?php
//Front controller for societyleadership

//load some configuration settings
global $ini_array;
$ini_array = parse_ini_file(__DIR__ . '/society_leadership_config.ini', true);

//load libraries...@todo use composer autolading.
require(__DIR__ . '/src/SocietyDB.php');
require(__DIR__ . '/src/User.php');
require(__DIR__ . '/src/Route.php');
require(__DIR__ . '/src/MenuUtils.php');
require(__DIR__ . '/src/Request.php');
require(__DIR__ . '/src/Response.php');
require(__DIR__ . '/src/BaseController.php');
require(__DIR__ . '/src/ActionsControllers.php');
require(__DIR__ . '/src/Validator.php');
require(__DIR__ . '/src/society_lib.php');

// Send output to browser
\SocietyLeadership\render();
exit(0);