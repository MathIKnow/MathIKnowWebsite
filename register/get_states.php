<?php
include __DIR__ . '/../vendor/autoload.php';

use MathIKnow\CountryStateHelper;

if (isset($_GET['countryISO3'])) {
    $countryISO3 = $_GET['countryISO3'];
    $stateMapping = CountryStateHelper::getStateCodeToStateMapping($countryISO3);
    if ($stateMapping == null) {
        echo "<option value=''></option>";
        return;
    }
    foreach ($stateMapping as $code => $name) {
        echo "<option value='$code'>$name</option>";
    }
}