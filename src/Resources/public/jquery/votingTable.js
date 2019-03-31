(function ($) {
    /**
     *
     * @param userIsOwner
     * @param isClassTeacher
     * @constructor
     */
    EditTable = function (userIsOwner, isClassTeacher) {

        var self = this;

        // Public variables
        self.userIsOwner = false;
        self.isClassTeacher = false;

        // request_token
        self.request_token = null;

        // teacher id
        self.teacher = null;

        // subject id
        self.subject = null;

        // class id
        self.class = null;

        /**
         *
         * @param userIsOwner
         * @param isClassTeacher
         * @returns {_initialize}
         * @private
         */
        var _initialize = function (userIsOwner, isClassTeacher) {
            self.userIsOwner = userIsOwner;
            self.userIsClassTeacher = isClassTeacher;
            if (!self.userIsOwner) {
                $('span.fa-close').each(function () {
                    $(this).attr('title', 'keine Berechtigung');
                    $(this).on('click', function () {
                        alert("Keine Berechtigung diesen Datensatz zu loeschen.");
                    });
                });

                $('span.edit_icon').each(function () {
                    $(this).attr('title', 'keine Berechtigung');
                    $(this).on('click', function () {
                        alert("Keine Berechtigung diesen Datensatz zu bearbeiten.");
                    });
                });
            }

            if (self.userIsOwner) {
                $('.skillCell').on('click', function (event) {

                    if (!$(this).hasClass('active')) {
                        event.stopPropagation();
                        self.edit(this, $(this).attr('data-id'), 'edit_row');
                    }
                });

                $('.submit_button').on('click', function (event) {
                    event.stopPropagation();
                    self.resetTable();
                });

            }

            return this;
        };

        /**
         *
         * @param elLink
         * @param intValue
         * @param mode
         */
        this.deleteEntries = function (elLink, intValue, mode) {
            intValue = intValue.toString();
            if (!self.userIsOwner) return;
            var url = window.location.protocol + '//' + window.location.hostname + window.location.pathname;
            var request = $.ajax({
                url: url + '?isAjax=true&act=delete_row_or_col',
                method: "post",
                data: {
                    REQUEST_TOKEN: self.request_token,
                    mode: mode,
                    colOrRow: intValue,
                    teacher: self.teacher.toString(),
                    subject: self.subject.toString(),
                    class: self.class.toString()
                },
                dataType: 'json'
            });
            request.done(function (json) {
                if (json) {
                    if (json.status == 'deleted') {
                        self.resetTable();
                    }
                }
            });
            request.fail(function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                alert('Fehler: Die Anfrage konnte nicht gespeichert werden! Überprüfe die Internetverbindung.');
            });
        };

        /**
         *
         * @param elInput
         */
        this.sendToServer = function (elInput) {
            var elCell = $(elInput).closest('td');
            var intValue = elInput.value.toString().trim();

            if (intValue.match(/^[1-4]{0,1}$/)) {
                elInput.value = intValue;
                var match = elInput.id.match(/^skillInput_s_(.*)_k_(.*)$/);
                var intStudentId = match[1].toString();
                var intCriterium = match[2].toString();
                if (intValue == '') {
                    intValue = 0;
                }

                var url = window.location.protocol + '//' + window.location.hostname + window.location.pathname;
                var request = $.ajax({
                    url: url + '?isAjax=true&act=update',
                    method: 'post',
                    data: {
                        REQUEST_TOKEN: self.request_token,
                        student: intStudentId.toString(),
                        value: intValue.toString(),
                        skill: intCriterium.toString(),
                        teacher: self.teacher.toString(),
                        subject: self.subject.toString(),
                        class: self.class.toString()
                    },
                    dataType: 'json',

                    beforeSend: function (event, xhr) {
                        $('#requestStatusBox').find('p').each(function () {
                            $(this).css('visibility', 'visible');
                        });
                    }
                });

                request.done(function (json) {
                    if (json) {
                        if (json.status == 'success') {
                            $(elInput).addClass('valueSaved');
                            window.setTimeout(function () {
                                $(elInput).removeClass('valueSaved');
                                $('#requestStatusBox p').each(function () {
                                    $(this).css('visibility', 'hidden');
                                });
                            }, 2000);
                        } else {
                            if (json.message) {
                                alert(json.message)
                            }
                        }
                    }
                });

                request.fail(function (jqXHR, textStatus, errorThrown) {
                    console.log(jqXHR);
                    alert('Fehler: Die Anfrage konnte nicht gespeichert werden! Überprüfe die Internetverbindung.');
                });

            }
            else {
                alert("Bitte einen gültigen Wert eingeben.\n Gültige Werte sind 1, 2, 3 oder 4.");
                $(elInput).addClass('error');
                window.setTimeout(function () {
                    elInput.value = '';
                    $(elInput).removeClass('error');
                }, 1000);
            }
        };

        /**
         *
         * @param elementClick
         * @param intValue
         * @param mode
         */
        this.edit = function (elementClick, intValue, mode) {
            if (!this.userIsOwner) return;

            // reset table
            self.resetTable();

            if (mode == 'edit_row') var studentId = intValue;
            if (mode == 'edit_col') var criteriumId = intValue;

            //add the grey bgImage
            if (mode == 'edit_col') {
                $('td.description.col_' + intValue).each(function () {
                    var bg = $(this).css('background-image');
                    bg = bg.replace('bright', 'dark');
                    $(this).css('background-image', bg);
                });
            }

            var submitButton = $(elementClick).closest('tr').find('.submit_button')[0];
            $(submitButton).on('click', function (event) {
                event.stopPropagation();
                self.resetTable();
            });


            // colorize the background for the active row and insert a text field
            var cssSelector = (mode == 'edit_row') ? 'table.beurteilungstabelle td.row_' + studentId : 'table.beurteilungstabelle td.col_' + criteriumId;

            $(cssSelector).each(function () {
                $(this).addClass('active');
                $(this).removeAttr('title');
                if ($(this).hasClass('skillCell')) {
                    self.injectInputField(this);

                }
            });
        };


        /**
         *
         * @param json
         */
        this.appearCommentModal = function (json) {
            $("#commentModal").remove();
            $('#top').prepend(json.strModal);
            $("#commentModal").modal('show');
        };


        /**
         *
         * @param elementClick
         * @param studentId
         */
        this.getCommentModal = function (elementClick, studentId) {
            var url = window.location.protocol + '//' + window.location.hostname + window.location.pathname;
            var request = $.ajax({
                url: url + '?isAjax=true&act=get_comment_modal',
                method: 'post',
                data: {
                    REQUEST_TOKEN: self.request_token,
                    student: studentId.toString(),
                    teacher: self.teacher.toString(),
                    subject: self.subject.toString()
                },
                dataType: 'json',

                beforeSend: function (event, xhr) {
                    //
                }
            });
            request.done(function (json) {
                if (json) {
                    if (json.status == 'success') {
                        self.appearCommentModal(json);
                    }
                }
            });
            request.fail(function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                alert('Request fehlgeschlagen. Internet-Verbindung überprüfen!');
            });
        };


        /**
         *
         * @param elementClick
         */
        this.toggleVisibility = function (elementClick) {

            var id = $(elementClick).closest('tr').attr('data-id');

            var url = window.location.protocol + '//' + window.location.hostname + window.location.pathname;
            var request = $.ajax({
                url: url + '?isAjax=true&act=toggle_visibility',
                method: 'post',
                data: {
                    REQUEST_TOKEN: self.request_token,
                    id: id
                },
                dataType: 'json',

                beforeSend: function (event, xhr) {
                    //
                }
            });
            request.done(function (json) {
                if (json) {
                    if (json.status == 'success') {
                        var icon = $(elementClick).find('i');
                        icon.removeClass('fa-eye fa-eye-slash');
                        if (json.published > 0) {
                            icon.addClass('fa-eye');
                        } else {
                            icon.addClass('fa-eye-slash');
                        }
                    }
                }
            });
            request.fail(function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                alert('Request fehlgeschlagen. Internet-Verbindung überprüfen!');
            });


        };


        /**
         *
         * @param elementClick
         */
        this.editComment = function (elementClick) {

            $('#commentForm textarea').text('').val('');


            var id = $(elementClick).closest('tr').attr('data-id');
            var subject = $(elementClick).closest('tr').attr('data-subject');
            var student = $(elementClick).closest('tr').attr('data-student');

            var url = window.location.protocol + '//' + window.location.hostname + window.location.pathname;
            var request = $.ajax({
                url: url + '?isAjax=true&act=get_comment',
                method: 'post',
                data: {
                    REQUEST_TOKEN: self.request_token,
                    //student: student.toString(),
                    //subject: subject.toString(),
                    id: id
                },
                dataType: 'json',

                beforeSend: function (event, xhr) {
                    //
                }
            });
            request.done(function (json) {
                if (json) {
                    if (json.status == 'success') {
                        //alert(json.comment);
                        $('#commentForm textarea[name="comment"]').val(json.comment);
                        $('#commentForm input[name="id"]').attr('value', json.id);
                        $('#commentForm input[name="dateOfCreation"]').attr('value', json.dateOfCreation);
                        $('#commentForm input[name="dateOfCreation"]').val(json.dateOfCreation);
                        $('#commentTable').hide();
                        $('#globalOperations').hide();
                        $('#commentForm').show();
                    }
                }
            });
            request.fail(function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                alert('Request fehlgeschlagen. Internet-Verbindung überprüfen!');
            });


        };


        /**
         *
         * @param elementClick
         * @param studentId
         */
        this.saveComment = function () {

            var comment = $('#commentForm textarea').val();
            var id = $('#commentForm input[name="id"]').val();
            var dateOfCreation = $('#commentForm input[name="dateOfCreation"]').val();

            // Check date
            if (!/^(19|20)\d\d-(0\d|1[012])-(0\d|1\d|2\d|3[01])$/.test(dateOfCreation)) {
                alert('Gib ein gültiges Datum im Format yyyy-mm-dd ein!');
                return;
            }


            var url = window.location.protocol + '//' + window.location.hostname + window.location.pathname;
            var request = $.ajax({
                url: url + '?isAjax=true&act=save_comment',
                method: 'post',
                data: {
                    REQUEST_TOKEN: self.request_token,
                    id: id,
                    dateOfCreation: dateOfCreation,
                    comment: comment
                },
                dataType: 'json',

                beforeSend: function (event, xhr) {
                    //
                }
            });
            request.done(function (json) {
                console.log(json);
                if (json) {
                    if (json.status == 'success') {
                        $('#commentTable tr').remove();
                        $('#commentTable').html(json.tableRows);
                        $('#commentTable').show();
                        $('#globalOperations').show();
                        $('#commentForm').hide();
                    }
                }
            });
            request.fail(function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                alert('Request fehlgeschlagen. Internet-Verbindung überprüfen!');
            });
        };

        /**
         *
         */
        this.newComment = function (subject, student) {
            $('#commentForm textarea').text('').val('');

            var url = window.location.protocol + '//' + window.location.hostname + window.location.pathname;
            var request = $.ajax({
                url: url + '?isAjax=true&act=new_comment',
                method: 'post',
                data: {
                    REQUEST_TOKEN: self.request_token,
                    student: student,
                    subject: subject
                },
                dataType: 'json',

                beforeSend: function (event, xhr) {
                    //
                }
            });
            request.done(function (json) {
                //console.log(json);
                if (json) {
                    if (json.status == 'success') {
                        $('#commentTable tr').remove();
                        $('#commentTable').html(json.tableRows);
                        $('#commentTable').show();
                        $('#globalOperations').show();
                        $('#commentForm').hide();
                    }
                }
            });
            request.fail(function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                alert('Request fehlgeschlagen. Internet-Verbindung überprüfen!');
            });
        };


        /**
         *
         * @param elementClick
         */
        this.deleteComment = function (elementClick) {

            if (!confirm('Soll der Kommentar wirklich gelöscht werden?')) {
                return;
            }

            var id = $(elementClick).closest('tr').attr('data-id');
            var url = window.location.protocol + '//' + window.location.hostname + window.location.pathname;
            var request = $.ajax({
                url: url + '?isAjax=true&act=delete_comment',
                method: 'post',
                data: {
                    REQUEST_TOKEN: self.request_token,
                    id: id
                },
                dataType: 'json',

                beforeSend: function (event, xhr) {
                    //
                }
            });
            request.done(function (json) {
                if (json) {
                    if (json.status == 'success') {
                        $(elementClick).closest('tr').remove();
                    }
                }
            });
            request.fail(function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                alert('Request fehlgeschlagen. Internet-Verbindung überprüfen!');
            });
        };


        /**
         *
         * @param elRange
         */
        this.updateTeachersDeviationTolerance = function (elRange) {

            var self = this;

            $(elRange).attr('title', 'Abweichungstoleranz: ' + elRange.value);

            var url = window.location.protocol + '//' + window.location.hostname + window.location.pathname;
            var request = $.ajax({
                url: url + '?isAjax=true&act=updateTeachersDeviationTolerance',
                method: "post",
                data: {
                    REQUEST_TOKEN: self.request_token,
                    tolerance: elRange.value
                },
                dataType: 'json'
            });

            request.done(function (json) {
                if (json) {
                    if (json.status == 'success') {
                        self.resetTable();
                    }
                }
            });

            request.fail(function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                alert('Fehler: Die Anfrage konnte nicht gespeichert werden! Überprüfe die Internetverbindung.');
            });
        };

        /**
         *
         */
        this.resetTable = function () {
            $('.beurteilungstabelle .textField').each(function () {
                $(this).remove();
            });

            $('.beurteilungstabelle .active').each(function () {
                $(this).removeClass('active');
            });

            //set to bgImage with the white background
            var i = 0;
            $('td.description').each(function () {
                i++;
                $(this).css({'background-image': "url('bundles/markocupicbuf/gd/images.php?bgcolor=bright&kriterium=" + i + "')"});
            });

            var url = window.location.protocol + '//' + window.location.hostname + window.location.pathname;
            var request = $.ajax({
                url: url + '?isAjax=true&act=reset_table&class=' + self.class + '&subject=' + self.subject + '&teacher=' + self.teacher,
                method: 'post',
                data: {
                    REQUEST_TOKEN: self.request_token
                },
                dataType: 'json'
            });
            request.done(function (json) {
                if (json) {
                    $.each(json.rows, function (key, row) {
                        var match = key.match(/^student_(.*)$/);
                        var studentId = match[1].toString();
                        for (var col = 1; col <= 8; col++) {
                            if ($('#skillCell_s_' + studentId + '_k_' + col)) {
                                var cell = $('#skillCell_s_' + studentId + '_k_' + col);
                                var rating = row['skill' + col]['value'];

                                // set the title property (last change)
                                cell.removeAttr('title');
                                if (row['skill' + col]['date'] !== null && rating > 0 && !$(cell).hasClass('active')) {
                                    var lastChange = row['skill' + col]['date'];
                                    $(cell).attr('title', 'Bewertung vom: ' + lastChange);
                                }
                                if (rating == 0) {
                                    rating = '';
                                }
                                cell.find('.rating').text(rating);
                                if ($(cell).find('.deviation').length) {
                                    $(cell).find('.deviation')[0].remove();
                                }

                                // add the rating to the text fields value-property, this only if the text-field is opened
                                $(cell).find('.textField').attr('value', rating);

                                // display deviation
                                var deviation = row['skill' + col]['deviation'];
                                if (deviation != '') {
                                    // update deviation
                                    var color = row['skill' + col]['color'];

                                    var devSpan = $('<span/>', {
                                        html: '<br>Abw:<br>' + deviation,
                                        'class': 'deviation'
                                    }).appendTo($(cell));
                                    $(devSpan).css('color', '#' + color);

                                }
                            }
                        }
                    });
                }
            });
            request.fail(function (jqXHR, textStatus, errorThrown) {
                console.log(jqXHR);
                alert('Fehler: Die Anfrage konnte nicht gespeichert werden! Überprüfe die Internetverbindung.');
            });

        };

        /**
         *
         * @param elCell
         */
        this.injectInputField = function (elCell) {

            var match = elCell.id.match(/^skillCell_s_(.+?)_k_(.+?)$/);
            var studentId = match[1].toString();
            var col = match[2].toString();
            // for firefox, chrome, opera
            var rating = $(elCell).find('.rating').text().trim();

            // insert the textfield into the cell
            var inpField = $('<input/>', {
                'type': 'text',
                'name': 'skillInput_s_' + studentId + '_k_' + col,
                'id': 'skillInput_s_' + studentId + '_k_' + col,
                'class': 'textField',
                'tabindex': col.toString(),
                'value': rating,
                'size': '1',
                'maxlength': '1',
                'pattern': '[1-4]{1}'
            });
            $(inpField).hide();
            $(elCell).append(inpField);
            $(inpField).fadeIn();
            // add the onchange event to the input field
            $(inpField).on('focus', function (event) {
                event.stopPropagation();
                $(this).on('change', function (event) {
                    event.stopPropagation();
                    self.sendToServer(this);
                });
            });
        };

        // Launch constructor
        _initialize(userIsOwner, isClassTeacher);

    };
})(jQuery);
