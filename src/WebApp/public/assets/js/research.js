const CtrlSearch = {

    construct: function() {

        ViewSearch.construct();

    },


    ajax_exec: function (action, target, type, research) {
        if (type === undefined) type = 0;
        const page = 'search';
        const url = `/app/ajax/switch.php?a-request=${page}&a-action=${action}`;
        const request = {
            research: research,
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: request,
            dataType: 'html',
            success: function (code_html) {
                let baliseStart = "";
                let baliseEnd = "";
                if ($(target).length === 0) {
                    baliseStart = "<ul id=\"results-autocomplete\" class=\"white dropdown-content\">";
                    baliseEnd = "</ul>";
                    target = "nav";
                    type = 1;
                }
                if(target !== undefined) {
                    if (type === 0) {
                        $(target).html(baliseStart + code_html + baliseEnd);
                    }
                    else if (type === 1) {
                        $(baliseStart + code_html + baliseEnd).appendTo(target)
                    }
                }
                ViewSearch.autocomplete();
                ViewSearch.go();
                ViewSearch.replace();
            },

            error: function (result, status, error) {
            },

            complete: function (result, status) {
            }

        });

    }

};
export const ViewSearch = {

    classInputSearch: ".search-autocomplete",
    term: undefined,
    target: "#results-autocomplete",
    resultsItem: ".results-item",

    inputResearch: undefined,

    construct: function () {

        $(ViewSearch.classInputSearch).on('focus keyup', function () {
            ViewSearch.inputResearch = this;
            CtrlSearch.ajax_exec($(this).attr('data-search'), ViewSearch.target, 0, this.value);
            if ($(ViewSearch.target + ":focus").length > 0) ViewSearch.replace();
        });

        $(ViewSearch.classInputSearch).on('mousedown mouseup blur focus keyup click', function () {
            ViewSearch.inputResearch = this;
            const term = $(this).val();
            const forId = $(ViewSearch.inputResearch).attr('for');
            if (forId !== undefined && ViewSearch.term !== undefined && ViewSearch.term !== term) {
                $('#' + forId).val('');
            }
            ViewSearch.term = term;
        });


        $(ViewSearch.classInputSearch).on('blur', function () {
            if ($(ViewSearch.target + ":hover").length === 0) {
                $(ViewSearch.target).remove();
            }
        });

    },

    go: function () {
        $("nav " + ViewSearch.target + " " + ViewSearch.resultsItem + " a").on('click', function () {
            // SET TARGET INPUT
            const forId = $(ViewSearch.inputResearch).attr('for');
            const id = $(this).attr('data-id');
            if (forId !== undefined) {
                $('#' + forId).val(id);
            }
            // ACTIVE LINK
            const active = $(ViewSearch.inputResearch).attr('data-link');
            const link = $(this).attr('data-link');
            if (active !== undefined && active === "on" && link !== undefined) {
                document.location.replace(link);
            }


        });
    },

    autocomplete: function () {
        $(ViewSearch.target + " " + ViewSearch.resultsItem + " a").on('click', function () {
            if (ViewSearch.inputResearch !== undefined) {
                $(ViewSearch.inputResearch).val($(this).html());
                ViewSearch.term = $(ViewSearch.inputResearch).val();
                $(ViewSearch.target).remove();
            }
        });
    },

    replace: function () {

        const position = $(ViewSearch.inputResearch).position();
        const marginTop = $(ViewSearch.inputResearch).css('margin-top');
        const width = $(ViewSearch.inputResearch).outerWidth();
        const height = $(ViewSearch.inputResearch).innerHeight() - 3;

        $(ViewSearch.target).css('top', position.top + height + "px");
        $(ViewSearch.target).css('margin-top', marginTop);
        $(ViewSearch.target).css('left', position.left + "px");
        $(ViewSearch.target).css('width', width + "px");
        $(ViewSearch.target).css('opacity', '100');

    }

};


CtrlSearch.construct();

export default CtrlSearch;