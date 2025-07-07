<?php

include_once __DIR__ . "/incl.php";

echo \echoQuiz\api_echoQuiz::doApi( substr($_GET['path'] , 5));
