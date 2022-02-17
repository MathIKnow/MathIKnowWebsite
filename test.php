<?php

include __DIR__ . '/vendor/autoload.php';

use MathIKnow\Utilities;

echo Utilities::capitalizeName("TEST\n");
echo Utilities::capitalizeName("Test\n");
echo Utilities::capitalizeName("test\n");