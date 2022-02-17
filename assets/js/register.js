$(document).ready(function() {
    // Change state/region selection depending on country
    var countryDropdown = $("#geo_country");
    var regionDropdown = $("#geo_region");
    function updateRegionSelect() {
        var countryCode = countryDropdown.val();
        $.get("get_states.php?countryISO3=" + countryCode, function (data) {
            regionDropdown.html(data);
        });
    }
    updateRegionSelect();
    countryDropdown.change(function() {
        updateRegionSelect();
    });

    // Auto select timezone
    var timezoneDropdown = $("#timezone");
    var timezone = jstz.determine().name();
    var selectObject = $('#timezone option[value="' + timezone +'"]');
    if (selectObject.length >= 1) {
        timezoneDropdown.val(timezone);
    }
});
