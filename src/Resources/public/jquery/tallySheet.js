(function ($) {

    TallySheet = function (options) {

        // Private variables
        var self = this;
        var request_token = null;

        // Public variables
        self.options = null;

        /**
         *
         * private method (constructor)
         * @param options
         * @returns {TallySheet}
         * @private
         */
        var _initialize = function (options) {
            self.options = options;
            self.request_token = options.request_token;

            if (self.request_token == '') {
                alert('Request Token wurde nicht initialisiert.');
                return;
            }
            return self;
        };

        /**
         *
         * @param elCell
         * @param student
         * @param col
         */
        this.showInfoBox = function (elCell, student, col) {
            var elCell = $(elCell);
            $(elCell).on('mouseleave', function (event) {
                event.stopPropagation();
            });

            // vars
            var studentId = student;
            var skill = col;

            // create redquest id
            self.fireRequest(studentId, skill);
        };


        /**
         *
         * @param studentId
         * @param skillId
         */
        this.fireRequest = function (studentId, skillId) {

            var url = window.location.protocol + '//' + window.location.hostname + window.location.pathname;
            var request = $.ajax({
                url: url + '?isAjax=true&act=tally_sheet',
                method: 'post',
                data: {
                    studentId: studentId,
                    skillId: skillId,
                    REQUEST_TOKEN: self.request_token
                },
                dataType: 'json'
            });
            request.done(function (json) {
                if (json) {

                    if (json.status == 'success') {
                        self.appearInfoBox(json);
                    }
                }
            });
            request.fail(function (jqXHR, textStatus, errorThrown) {
                alert('Request fehlgeschlagen. Internet-Verbindung überprüfen!');
            });
        };

        /**
         *
         * @param json
         */
        this.appearInfoBox = function (json) {
            $("#tallysheetModal").remove();
            $('#top').prepend(json.html);
            $("#tallysheetModal").modal('show');
        };


        /**
         *  Launch Constructor
         */
        _initialize(options);
    };

})(jQuery);