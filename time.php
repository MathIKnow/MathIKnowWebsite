<?php

include __DIR__ . '/vendor/autoload.php';

use MathIKnow\TimeHelper;

echo TimeHelper::getUTCTimestamp();
echo $_SERVER['REMOTE_ADDR'];