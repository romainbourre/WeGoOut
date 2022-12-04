$(".button-collapse").sideNav();

export const $_GET = (param) => {
    var vars = {};
    window.location.href.replace(location.hash, '').replace(
        /[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
        function (m, key, value) { // callback
            vars[key] = value !== undefined ? value : '';
        }
    );

    if (param) {
        return vars[param] ? vars[param] : '';
    }
    return vars;
}




function responsive() {
    var nav = $('.nav-wrapper').css('height');
    $('main, .sail').css('min-height', 'calc(100vh - ' + nav + ')');
}
responsive();
$(window).resize(function () {
    responsive();
});

$(document).ready(function () {
    $("#notifications-button").dropdown();
});