(function ($) {
    /**
     *
     * @param userIsOwner
     * @param isClassTeacher
     * @constructor
     */
    AverageTable = function (teacherId, requestToken) {

        var self = this;

        // request_token
        self.request_token = null;

        // teacher
        self.teacherId = null;


        /**
         *
         * @param teacherId
         * @param requestToken
         * @returns {_initialize}
         * @private
         */
        var _initialize = function (teacherId, requestToken) {
            self.teacherId = teacherId;
            self.request_token = requestToken;
            return this;
        };

        /**
         *
         * @param elSelect
         */
        this.updateTeachersShowCommentsTimeRange = function (elSelect) {
            var timeRange = $(elSelect).prop('value');
            var url = window.location.protocol + '//' + window.location.hostname + window.location.pathname;             var request = $.ajax({
                url: url + '?isAjax=true&act=updateTeachersShowCommentsTimeRange',
                method: "post",
                data: {
                    REQUEST_TOKEN: self.request_token,
                    timeRange: timeRange.toString(),
                    teacherId: self.teacherId.toString(),
                },
                dataType: 'json'
            });
            request.done(function (json) {
                if (json) {
                    if (json.status == 'success') {
                        //alert(json.timeRange)
                    }
                }
            });
            request.fail(function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                alert('Fehler: Die Anfrage konnte nicht gespeichert werden! Überprüfe die Internetverbindung.');
            });
        };

        // Launch constructor
        _initialize(teacherId, requestToken);


    };
})(jQuery);
