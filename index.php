<?php

//Front controller for societyleadership

require(__DIR__ . '/society_lib.php');

// /member/sign-up
// /member/sign-up

// Send output to browser

\SocietyLeadership\render_view();
exit();