var ViewValidation = {

    idValidationCard: '.validation-information',

    construct: function() {

        ViewValidation.layoutCSS();

        $(window).resize(function () {
            ViewValidation.layoutCSS();
        });

    },

    layoutCSS: function() {

        var mainHeight = $('main').css('height');
        var cardValidationHeight = $(ViewValidation.idValidationCard).css('height');
        if (window.innerWidth > 1200) {
            $(ViewValidation.idValidationCard).css('margin-top', 'calc((' + mainHeight + ' - ' + cardValidationHeight + ')/2)');
        }
        else {
            $(ViewValidation.idValidationCard).css('margin-top', '75px');
            $(ViewValidation.idValidationCard).css('margin-bottom', '75px');

        }
        if(window.innerWidth < 600) {
            $(ViewValidation.idValidationCard).css('margin-top', '0px');
            $(ViewValidation.idValidationCard).css('margin-bottom', '0px');
        }
        else {
            $(ViewValidation.idValidationCard).css('margin-bottom', 'calc((' + mainHeight + ' - ' + cardValidationHeight + ')/2)');
        }

    }

};

var CtrlValidation = {

    construct: function () {

        ViewValidation.construct();

    }

};

CtrlValidation.construct();
