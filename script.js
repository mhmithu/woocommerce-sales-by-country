jQuery(".wsc_datepicker").datepicker({
    changeMonth: true,
    changeYear: true,
    defaultDate: "",
    dateFormat: "yy-mm-dd",
    numberOfMonths: 1,
    maxDate: "+0D",
    showButtonPanel: true,
    showOn: "focus",
    buttonImageOnly: true,
    onSelect: function( selectedDate ) {
        var option = jQuery(this).is('.from') ? "minDate" : "maxDate",
            instance = jQuery(this).data("datepicker"),
            date = jQuery.datepicker.parseDate(
                instance.settings.dateFormat ||
                jQuery.datepicker._defaults.dateFormat,
                selectedDate, instance.settings);
        dates.not(this).datepicker("option", option, date);
    }
});
