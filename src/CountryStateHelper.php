<?php

namespace MathIKnow;

class CountryStateHelper {
    private static $expirationSeconds = (48 * 60) * 60; // 48 hours

    public static function getDataFile() {
        return __DIR__ . "/countries+states.json";
    }

    public static function updateData() {
        $url = 'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/countries%2Bstates.json';
        $data = file_get_contents($url);
        file_put_contents(self::getDataFile(), $data);
        return $data;
    }

    public static function getData() {
        if (!file_Exists(self::getDataFile()) || time() - filemtime(self::getDataFile()) > self::$expirationSeconds) {
            return json_decode(self::updateData(), true);
        }
        return json_decode(file_get_contents(self::getDataFile()), true);
    }

    public static function getCountryNames() {
        $data = self::getData();
        $countryNames = [];
        foreach ($data as $country) {
            $countryNames[] = $country['name'];
        }
        return $countryNames;
    }

    public static function getCountryISO3List() {
        $data = self::getData();
        $countryISO3List = [];
        foreach($data as $country) {
            $countryISO3List[] = $country['iso3'];
        }
        return $countryISO3List;
    }

    public static function getISO3ToCountryMapping() {
        $data = self::getData();
        $iso3Associative = [];
        foreach ($data as $country) {
            $iso3Associative[$country['iso3']] = $country['name'];
        }
        return $iso3Associative;
    }

    public static function getCountryByISO3($ISO3) {
        $data = self::getData();
        foreach ($data as $country) {
            if ($country['iso3'] == $ISO3) {
                return $country;
            }
        }
        return null;
    }

    public static function getStateCodes($countryISO3) {
        return array_keys(self::getStateCodeToStateMapping($countryISO3));
    }

    public static function getStateCodeToStateMapping($countryISO3) {
        $country = self::getCountryByISO3($countryISO3);
        if (!$country) {
            return null;
        }
        $stateMapping = [];
        foreach ($country['states'] as $state) {
            $stateMapping[$state['state_code']] = $state['name'];
        }
        return $stateMapping;
    }
}