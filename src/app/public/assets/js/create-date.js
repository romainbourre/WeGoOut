import ViewCreateEvent from "./create-view.js";

const dateEvent = {

    dateBegin: new Date(),
    activeDateEnd: null,
    dateEnd: null,


    construct: function () {
        dateEvent.dateBegin.setHours((dateEvent.dateBegin).getHours() + 1);
        dateEvent.dateEnd = new Date(dateEvent.dateBegin.getTime() + (3600 * 1000));
        ViewCreateEvent.set_date_begin((dateEvent.dateBegin.getDate()).toString().replace(/^(\d)$/, '0$1') + '/' + (dateEvent.dateBegin.getMonth() + 1).toString().replace(/^(\d)$/, '0$1') + '/' + dateEvent.dateBegin.getFullYear());
        ViewCreateEvent.set_time_begin((dateEvent.dateBegin.getHours()).toString().replace(/^(\d)$/, '0$1') + ':' + (dateEvent.dateBegin.getMinutes()).toString().replace(/^(\d)$/, '0$1'));
        ViewCreateEvent.set_date_end((dateEvent.dateEnd.getDate()).toString().replace(/^(\d)$/, '0$1') + '/' + (dateEvent.dateEnd.getMonth() + 1).toString().replace(/^(\d)$/, '0$1') + '/' + dateEvent.dateEnd.getFullYear());
        ViewCreateEvent.set_time_end((dateEvent.dateEnd.getHours()).toString().replace(/^(\d)$/, '0$1') + ':' + (dateEvent.dateEnd.getMinutes()).toString().replace(/^(\d)$/, '0$1'));
    },

    update: function () {
        dateEvent.dateBegin.setTime(Date.parse(ViewCreateEvent.get_datetime_begin()));
        dateEvent.dateEnd.setTime(Date.parse(ViewCreateEvent.get_datetime_end()));
        dateEvent.activeDateEnd = ViewCreateEvent.status_datetime_end();
    },

    /**
     * Check dates and times of the event
     * @param display true(default)|false displaying error messages
     * @returns {boolean}
     */
    check_date: function (display) {
        dateEvent.update();
        const timestampBegin = dateEvent.dateBegin.getTime();
        const timestampEnd = dateEvent.dateEnd.getTime();
        if (display === undefined) display = true;

        let run = true;

        if (timestampBegin < (new Date().getTime())) {
            if (display) {
                $(ViewCreateEvent.idDateBegin + ',' + ViewCreateEvent.idTimeBegin).removeClass('valid');
                $(ViewCreateEvent.idDateBegin + ',' + ViewCreateEvent.idTimeBegin).addClass('invalid');
                $(ViewCreateEvent.idDatetimeBeginFeedback).html("La date et l'heure de début ne peut être inférieur à la date et l'heure d'aujourd'hui");
            }
            run = false;
        } else {
            if (display) {
                $(ViewCreateEvent.idDateBegin + ',' + ViewCreateEvent.idTimeBegin).removeClass('invalid');
                $(ViewCreateEvent.idDateBegin + ',' + ViewCreateEvent.idTimeBegin).addClass('valid');
                $(ViewCreateEvent.idDatetimeBeginFeedback).html('');
            }
        }

        if (dateEvent.activeDateEnd && timestampBegin < timestampEnd && timestampBegin !== timestampEnd) {
            if (display) {
                $(ViewCreateEvent.idDateEnd + ',' + ViewCreateEvent.idTimeEnd).removeClass('invalid');
                $(ViewCreateEvent.idDateEnd + ',' + ViewCreateEvent.idTimeEnd).addClass('valid');
                $(ViewCreateEvent.idDatetimeEndFeedback).html('');
            }
        } else if (dateEvent.activeDateEnd && timestampBegin >= timestampEnd) {
            if (display) {
                $(ViewCreateEvent.idDateEnd + ',' + ViewCreateEvent.idTimeEnd).removeClass('valid');
                $(ViewCreateEvent.idDateEnd + ',' + ViewCreateEvent.idTimeEnd).addClass('invalid');
                $(ViewCreateEvent.idDatetimeEndFeedback).html('Les dates de début et de fin sont incohérentes, vous avez trop regardé "Retour vers le futur" !');
            }
            run = false;
        } else {
            if (display) {
                $(ViewCreateEvent.idDateEnd + ',' + ViewCreateEvent.idTimeEnd).removeClass('invalid');
                $(ViewCreateEvent.idDateEnd + ',' + ViewCreateEvent.idTimeEnd).removeClass('valid');
                $(ViewCreateEvent.idDatetimeEndFeedback).html('');
            }
        }

        return run;

    }

};

export default dateEvent;