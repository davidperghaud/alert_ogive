$(function () {
    $.datetimepicker.setLocale('fr');
    $('#publicationDate').datetimepicker({
        timepicker: false,
        //minDate: '0',
        format: 'd-m-Y',
        lang: 'fr',
        scrollInput: false
    });
    $("#openingDate").datetimepicker({
        minDate: '0',
        format: 'd-m-Y H:i',
        lang: 'fr',
        scrollInput: false
    });
    $("#deadline").datetimepicker({
        minDate: '0',
        format: 'd-m-Y H:i',
        lang: 'fr',
        scrollInput: false
    });
    $('#ogive_alertbundle_expressionInterest_domain.ui.dropdown').dropdown({
        on: 'click'
    });
    $('#ogive_alertbundle_expressionInterest_subDomain.ui.dropdown').dropdown({
        on: 'click'
    });
    $('#add_expressionInterest_btn').click(function () {
        $('#add_expressionInterest.ui.modal').modal('setting', {
            autofocus: false,
            inverted: true,
            closable: false
        });
        $('#add_expressionInterest.ui.modal').modal('show');
    });
    $('#cancel_add_expressionInterest').click(function () {
        window.location.reload();
    });

    $('#add_expressionInterest_form.ui.form')
            .form({
                fields: {
                    reference: {
                        identifier: 'reference',
                        rules: [
                            {
                                type: 'empty',
                                prompt: 'Veuillez saisir le numero'
                            }
                        ]
                    },
                    object: {
                        identifier: 'object',
                        rules: [
                            {
                                type: 'empty',
                                prompt: "Veuillez saisir l'objet"
                            }
                        ]
                    },
                    owner: {
                        identifier: 'owner',
                        rules: [
                            {
                                type: 'empty',
                                prompt: "Veuillez renseigner le maître d'ouvrage"
                            }
                        ]
                    },
                    domain: {
                        identifier: 'domain',
                        rules: [
                            {
                                type: 'empty',
                                prompt: "Veuillez selectionner le domaine"
                            }
                        ]
                    },
                    publication_date: {
                        identifier: 'publication_date',
                        rules: [
                            {
                                type: 'empty',
                                prompt: "Veuillez renseigner la date de publication de l'offre"
                            }
                        ]
                    },
                    opening_date: {
                        identifier: 'opening_date',
                        rules: [
                            {
                                type: 'empty',
                                prompt: "Veuillez renseigner la date d'ouverture de dépôt"
                            }
                        ]
                    },
                    deadline_date: {
                        identifier: 'deadline_date',
                        rules: [
                            {
                                type: 'empty',
                                prompt: "Veuillez renseigner la date limite de dépôt"
                            }
                        ]
                    }

                },
                inline: true,
                on: 'change'
//                onSuccess: function (event, fields) {
//                    $.ajax({
//                        type: 'post',
//                        url: Routing.generate('expressionInterest_add'),
//                        data: fields,
//                        dataType: 'json',
//                        beforeSend: function () {
//                            $('#submit_expressionInterest').addClass('disabled');
//                            $('#cancel_add_expressionInterest').addClass('disabled');
//                            $('#add_expressionInterest_form.ui.form').addClass('loading');
//                        },
//                        statusCode: {
//                            500: function (xhr) {
//                                $('#server_error_message').show();
//                            },
//                            400: function (response, textStatus, jqXHR) {
//                                console.log(response);
//                                var myerrors = response.responseJSON;
//                                if (myerrors.success === false) {
//                                    $('#error_name_header').html("Echec de la validation");
//                                    $('#error_name_list').html('<li>' + myerrors.message + '</li>');
//                                    $('#error_name_message').show();
//                                } else {
//                                    $('#error_name_header').html("Echec de la validation. Veuillez vérifier vos données");
//                                    $('#error_name_message').show();
//                                }
//
//                            }
//                        },
//                        success: function (response, textStatus, jqXHR) {
//                            $('#cancel_add_expressionInterest').removeClass('disabled');
//                            $('#submit_expressionInterest').removeClass('disabled');
//                            $('#add_expressionInterest_form.ui.form').removeClass('loading');
////                                $('#list_as_grid_content').prepend(response.expressionInterest_content_grid);
////                                $('#list_as_table_content').prepend(response.expressionInterest_content_list);
////                                $('.ui.dropdown').dropdown({
////                                    on: 'hover'
////                                });
//                            $('#add_expressionInterest.ui.modal').modal('hide');
//                            $('#message_success>div.header').html(response.message);
//                            $('#message_success').show();
//                            window.location.replace(Routing.generate('expressionInterest_index'));
//                            setTimeout(function () {
//                                $('#message_success').hide();
//                            }, 4000);
//
//
//                        },
//                        error: function (jqXHR, textStatus, errorThrown) {
//                            $('#cancel_add_expressionInterest').removeClass('disabled');
//                            $('#submit_expressionInterest').removeClass('disabled');
//                            $('#add_expressionInterest_form.ui.form').removeClass('loading');
//                            /*alertify.error("Internal Server Error");*/
//                        }
//                    });
//                    return false;
//                }
            }
            );

    $('#submit_expressionInterest').click(function (e) {
        e.preventDefault();
        $('#server_error_message').hide();
        $('#error_name_message').hide();
        $('#error_name_message_edit').hide();
        if ($('#add_expressionInterest_form.ui.form').form('is valid')) {
            $.ajax({
                type: 'post',
                url: Routing.generate('expressionInterest_add'),
                data: {'testunicity': 'yes', 'reference': $('#add_expressionInterest_form.ui.form input[name*="reference"]').val()},
                dataType: 'json',
                beforeSend: function () {
                    $('#submit_expressionInterest').addClass('disabled');
                    $('#cancel_add_expressionInterest').addClass('disabled');
                    $('#add_expressionInterest_form.ui.form').addClass('loading');
                },
                statusCode: {
                    500: function (xhr) {
                        $('#server_error_message').show();
                    },
                    400: function (response, textStatus, jqXHR) {
                        var myerrors = response.responseJSON;
                        if (myerrors.success === false) {
                            $('#error_name_header').html("Echec de la validation");
                            $('#error_name_list').html('<li>' + myerrors.message + '</li>');
                            $('#error_name_message').show();
                        } else {
                            $('#error_name_header').html("Echec de la validation. Veuillez vérifier vos données");
                            $('#error_name_message').show();
                        }

                    }
                },
                success: function (response, textStatus, jqXHR) {
                    $('#add_expressionInterest_form.ui.form').submit();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#cancel_add_expressionInterest').removeClass('disabled');
                    $('#submit_expressionInterest').removeClass('disabled');
                    $('#add_expressionInterest_form.ui.form').removeClass('loading');
                }
            });
        }
    });
});

function edit_expressionInterest(id) {
    $('#message_error').hide();
    $('#message_success').hide();
    $('.ui.dropdown').dropdown('remove active');
    $('.ui.dropdown').dropdown('remove visible');
    $('.ui.dropdown>div.menu').removeClass('visible');
    $('.ui.dropdown>div.menu').addClass('hidden');
    $.ajax({
        type: 'PUT',
        url: Routing.generate('expressionInterest_update', {id: id}),
        dataType: 'json',
        beforeSend: function () {
            $('#message_loading').show();
        },
        statusCode: {
            500: function (xhr) {
                $('#server_error_message').show();
            },
            404: function (response, textStatus, jqXHR) {
                $('#message_error>div.header').html(response.responseJSON.message);
                $('#message_error').show();
            }
        },
        success: function (response, textStatus, jqXHR) {
            $('#edit_expressionInterest').remove();
            $('#edit_expressionInterest_content').html(response.edit_expressionInterest_form);
            $('#edit_expressionInterest.ui.modal').modal('setting', {
                autofocus: false,
                inverted: true,
                closable: false
            });
            $('#ogive_alertbundle_expressionInterest_domain.ui.dropdown').dropdown({
                on: 'click'
            });
            $('#ogive_alertbundle_expressionInterest_subDomain.ui.dropdown').dropdown({
                on: 'click'
            });
            $('#cancel_edit_expressionInterest').click(function () {
                window.location.reload();
            });
            $('#edit_expressionInterest.ui.modal').modal('show');
            $("#openingDate_edit").datetimepicker({
                minDate: '0',
                format: 'd-m-Y H:i',
                lang: 'fr',
                scrollInput: false
            });
            $("#deadline_edit").datetimepicker({
                minDate: '0',
                format: 'd-m-Y H:i',
                lang: 'fr',
                scrollInput: false
            });
            $('#publicationDate_edit').datetimepicker({
                timepicker: false,
                format: 'd-m-Y',
                lang: 'fr',
                scrollInput: false
            });
            execute_edit(id);

            $('#message_loading').hide();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $('#message_loading').hide();
        }
    });
}

function execute_edit(id) {

    $('#edit_expressionInterest_form.ui.form')
            .form({
                fields: {
                    reference: {
                        identifier: 'reference',
                        rules: [
                            {
                                type: 'empty',
                                prompt: 'Veuillez saisir le numero'
                            }
                        ]
                    },
                    object: {
                        identifier: 'object',
                        rules: [
                            {
                                type: 'empty',
                                prompt: "Veuillez saisir l'objet"
                            }
                        ]
                    },
                    owner: {
                        identifier: 'owner',
                        rules: [
                            {
                                type: 'empty',
                                prompt: "Veuillez renseigner le maître d'ouvrage"
                            }
                        ]
                    },
                    domain: {
                        identifier: 'domain',
                        rules: [
                            {
                                type: 'empty',
                                prompt: "Veuillez selectionner le domaine"
                            }
                        ]
                    },
                    publication_date: {
                        identifier: 'publication_date',
                        rules: [
                            {
                                type: 'empty',
                                prompt: "Veuillez renseigner la date de publication de l'offre"
                            }
                        ]
                    },
                    opening_date: {
                        identifier: 'opening_date',
                        rules: [
                            {
                                type: 'empty',
                                prompt: "Veuillez renseigner la date d'ouverture de dépôt"
                            }
                        ]
                    },

                    deadline_date: {
                        identifier: 'deadline_date',
                        rules: [
                            {
                                type: 'empty',
                                prompt: "Veuillez renseigner la date limite de dépôt"
                            }
                        ]
                    }
                },
                inline: true,
                on: 'change'
//                onSuccess: function (event, fields) {
//                    $.ajax({
//                        type: 'PUT',
//                        url: Routing.generate('expressionInterest_update', {id: id}),
//                        data: fields,
//                        dataType: 'json',
//                        beforeSend: function () {
//                            $('#submit_edit_expressionInterest').addClass('disabled');
//                            $('#cancel_edit_expressionInterest').addClass('disabled');
//                            $('#edit_expressionInterest_form.ui.form').addClass('loading');
//                            $('#cancel_details_expressionInterest').addClass('disabled');
//                            $('#disable_expressionInterest').addClass('disabled');
//                            $('#enable_expressionInterest').addClass('disabled');
//                        },
//                        statusCode: {
//                            500: function (xhr) {
//                                $('#server_error_message_edit').show();
//                            },
//                            400: function (response, textStatus, jqXHR) {
//                                var myerrors = response.responseJSON;
//                                if (myerrors.success === false) {
//                                    $('#error_name_header_edit').html("Echec de la validation");
//                                    $('#error_name_list_edit').html('<li>' + myerrors.message + '</li>');
//                                    $('#error_name_message_edit').show();
//                                } else {
//                                    $('#error_name_header_edit').html("Echec de la validation. Veuillez vérifier vos données");
//                                    $('#error_name_message_edit').show();
//                                }
//
//                            }
//                        },
//                        success: function (response, textStatus, jqXHR) {
//                            $('#submit_edit_expressionInterest').removeClass('disabled');
//                            $('#cancel_edit_expressionInterest').removeClass('disabled');
//                            $('#edit_expressionInterest_form.ui.form').removeClass('loading');
//                            $('#cancel_details_expressionInterest').removeClass('disabled');
//                            $('#disable_expressionInterest').removeClass('disabled');
//                            $('#enable_expressionInterest').removeClass('disabled');
////                                $('#expressionInterest_grid' + id).html(response.expressionInterest_content_grid);
////                                $('#expressionInterest_list' + id).html(response.expressionInterest_content_list);
////                                $('.ui.dropdown').dropdown({
////                                    on: 'hover'
////                                });
//                            $('#edit_expressionInterest.ui.modal').modal('hide');
//                            $('#message_success>div.header').html(response.message);
//                            $('#message_success').show();
//                            window.location.replace(Routing.generate('expressionInterest_index'));
//                            setTimeout(function () {
//                                $('#message_success').hide();
//                            }, 4000);
//                            $('#edit_expressionInterest').remove();
//
//                        },
//                        error: function (jqXHR, textStatus, errorThrown) {
//                            $('#submit_edit_expressionInterest').removeClass('disabled');
//                            $('#cancel_edit_expressionInterest').removeClass('disabled');
//                            $('#edit_expressionInterest_form.ui.form').removeClass('loading');
//                            /*alertify.error("Internal Server Error");*/
//                        }
//                    });
//                    return false;
//                }
            }
            );

    $('#submit_edit_expressionInterest').click(function (e) {
        e.preventDefault();
        $('#server_error_message').hide();
        $('#error_name_message').hide();
        $('#error_name_message_edit').hide();
        if ($('#edit_expressionInterest_form.ui.form').form('is valid')) {
            $.ajax({
                type: 'PUT',
                url: Routing.generate('expressionInterest_update', {id: id}),
                data: {'testunicity': 'yes', 'reference': $('#edit_expressionInterest_form.ui.form input[name*="reference"]').val()},
                dataType: 'json',
                beforeSend: function () {
                    $('#submit_edit_expressionInterest').addClass('disabled');
                    $('#cancel_edit_expressionInterest').addClass('disabled');
                    $('#edit_expressionInterest_form.ui.form').addClass('loading');
                    $('#cancel_details_expressionInterest').addClass('disabled');
                    $('#disable_expressionInterest').addClass('disabled');
                    $('#enable_expressionInterest').addClass('disabled');
                },
                statusCode: {
                    500: function (xhr) {
                        $('#server_error_message_edit').show();
                    },
                    400: function (response, textStatus, jqXHR) {
                        var myerrors = response.responseJSON;
                        if (myerrors.success === false) {
                            $('#error_name_header_edit').html("Echec de la validation");
                            $('#error_name_list_edit').html('<li>' + myerrors.message + '</li>');
                            $('#error_name_message_edit').show();
                        } else {
                            $('#error_name_header_edit').html("Echec de la validation. Veuillez vérifier vos données");
                            $('#error_name_message_edit').show();
                        }

                    }
                },
                success: function (response, textStatus, jqXHR) {
                    $('#edit_expressionInterest_form.ui.form').submit();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $('#submit_edit_expressionInterest').removeClass('disabled');
                    $('#cancel_edit_expressionInterest').removeClass('disabled');
                    $('#edit_expressionInterest_form.ui.form').removeClass('loading');
                }
            });
        }
    });
}

function delete_expressionInterest(id) {
    $('#confirm_delete_expressionInterest.ui.small.modal')
            .modal('show');

    $('#execute_delete_expressionInterest').click(function (e) {
        e.preventDefault();
        $('#confirm_delete_expressionInterest.ui.small.modal')
                .modal('hide');
        $('#message_error').hide();
        $('#message_success').hide();
        $('.ui.dropdown').dropdown('remove active');
        $('.ui.dropdown').dropdown('remove visible');
        $('.ui.dropdown>div.menu').removeClass('visible');
        $('.ui.dropdown>div.menu').addClass('hidden');
        $.ajax({
            type: 'DELETE',
            url: Routing.generate('expressionInterest_delete', {id: id}),
            dataType: 'json',
            beforeSend: function () {
                $('#message_loading').show();
            },
            statusCode: {
                500: function (xhr) {
                    $('#message_error>div.header').html("Erreur s'est produite au niveau du serveur");
                    $('#message_error').show();

                },
                404: function (response, textStatus, jqXHR) {
                    $('#message_error>div.header').html(response.responseJSON.message);
                    $('#message_error').show();

                }
            },
            success: function (response, textStatus, jqXHR) {
                $('#expressionInterest_grid' + id).remove();
                $('#expressionInterest_list' + id).remove();
                $('#message_loading').hide();
                $('#message_success>div.header').html(response.message);
                $('#message_success').show();
                window.location.replace(Routing.generate('expressionInterest_index'));
                setTimeout(function () {
                    $('#message_success').hide();
                }, 4000);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('#message_loading').hide();
            }
        });
    });
}

function show_expressionInterest(id) {
    $('#message_error').hide();
    $('#message_success').hide();
    $('.ui.dropdown').dropdown('remove active');
    $('.ui.dropdown').dropdown('remove visible');
    $('.ui.dropdown>div.menu').removeClass('visible');
    $('.ui.dropdown>div.menu').addClass('hidden');
    $.ajax({
        type: 'GET',
        url: Routing.generate('expressionInterest_get_one', {id: id}),
        dataType: 'json',
        beforeSend: function () {
            $('#message_loading').show();
        },
        statusCode: {
            500: function (xhr) {
                $('#message_error>div.header').html("Erreur s'est produite au niveau du serveur");
                $('#message_error').show();
            },
            404: function (response, textStatus, jqXHR) {
                $('#message_error>div.header').html(response.responseJSON.message);
                $('#message_error').show();
            }
        },
        success: function (response, textStatus, jqXHR) {
            $('#edit_expressionInterest').remove();
            $('#edit_expressionInterest_content').html(response.expressionInterest_details);
            $('#edit_expressionInterest.ui.modal').modal('setting', {
                autofocus: false,
                inverted: true,
                closable: false
            });
            $('#ogive_alertbundle_expressionInterest_domain.ui.dropdown').dropdown({
                on: 'click'
            });
            $('#ogive_alertbundle_expressionInterest_subDomain.ui.dropdown').dropdown({
                on: 'click'
            });
            $('#cancel_details_expressionInterest').click(function () {
                window.location.reload();
            });

            $('#edit_expressionInterest.ui.modal').modal('show');
            $("#openingDate_edit").datetimepicker({
                minDate: '0',
                format: 'd-m-Y H:i',
                lang: 'fr',
                scrollInput: false
            });
            $("#deadline_edit").datetimepicker({
                minDate: '0',
                format: 'd-m-Y H:i',
                lang: 'fr',
                scrollInput: false
            });
            $('#publicationDate_edit').datetimepicker({
                timepicker: false,
                format: 'd-m-Y',
                lang: 'fr',
                scrollInput: false
            });
            execute_edit(id);
            $('#edit_expressionInterest_btn').click(function () {
                $('#block_details').hide();
                $('#block_form_edit').show();
                $('#cancel_edit_expressionInterest').show();
                $('#submit_edit_expressionInterest').show();
                $(this).hide();
            });
            $('#cancel_edit_expressionInterest').click(function () {
                $('#block_details').show();
                $('#block_form_edit').hide();
                $('#edit_expressionInterest_btn').show();
                $('#submit_edit_expressionInterest').hide();
                $(this).hide();
            });

            $('#message_loading').hide();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $('#message_loading').hide();
        }
    });
}

function enable_expressionInterest(id) {
    $('#confirm_disable_expressionInterest.ui.small.modal')
            .modal('show');

    $('#execute_disable_expressionInterest').click(function (e) {
        e.preventDefault();
        $('#confirm_disable_expressionInterest.ui.small.modal')
                .modal('hide');
        $('#message_error').hide();
        $('#message_success').hide();
        $('#edit_expressionInterest.ui.modal').modal('hide');
        $('#edit_expressionInterest').remove();
        $('.ui.dropdown').dropdown('remove active');
        $('.ui.dropdown').dropdown('remove visible');
        $('.ui.dropdown>div.menu').removeClass('visible');
        $('.ui.dropdown>div.menu').addClass('hidden');
        $.ajax({
            type: 'PUT',
            url: Routing.generate('expressionInterest_update', {id: id}),
            data: {'action': 'enable'},
            dataType: 'json',
            beforeSend: function () {
                $('#message_loading').show();
            },
            statusCode: {
                500: function (xhr) {
                    $('#message_error>div.header').html("Erreur s'est produite au niveau du serveur");
                    $('#message_error').show();

                },
                404: function (response, textStatus, jqXHR) {
                    $('#message_error>div.header').html("Echec d'activation de la manifestation");
                    $('#message_error').show();

                }
            },
            success: function (response, textStatus, jqXHR) {
                $('#message_loading').hide();
                $('#enable_expressionInterest_grid' + id).hide();
                $('#disable_expressionInterest_grid' + id).show();
                $('#message_success>div.header').html(response.message);
                $('#message_success').show();
                window.location.replace(Routing.generate('expressionInterest_index'));
                setTimeout(function () {
                    $('#message_success').hide();
                }, 4000);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('#message_loading').hide();
            }
        });
    });
}

function disable_expressionInterest(id) {
    $('#confirm_disable_expressionInterest.ui.small.modal')
            .modal('show');

    $('#execute_disable_expressionInterest').click(function (e) {
        e.preventDefault();
        $('#confirm_disable_expressionInterest.ui.small.modal')
                .modal('hide');
        $('#message_error').hide();
        $('#message_success').hide();
        $('#edit_expressionInterest.ui.modal').modal('hide');
        $('#edit_expressionInterest').remove();
        $('.ui.dropdown').dropdown('remove active');
        $('.ui.dropdown').dropdown('remove visible');
        $('.ui.dropdown>div.menu').removeClass('visible');
        $('.ui.dropdown>div.menu').addClass('hidden');
        $.ajax({
            type: 'PUT',
            url: Routing.generate('expressionInterest_update', {id: id}),
            data: {'action': 'disable'},
            dataType: 'json',
            beforeSend: function () {
                $('#message_loading').show();
            },
            statusCode: {
                500: function (xhr) {
                    $('#message_error>div.header').html("Erreur s'est produite au niveau du serveur");
                    $('#message_error').show();

                },
                404: function (response, textStatus, jqXHR) {
                    $('#message_error>div.header').html("Echec de la désactivation de la manifestition");
                    $('#message_error').show();

                }
            },
            success: function (response, textStatus, jqXHR) {
                $('#message_loading').hide();
                $('#disable_expressionInterest_grid' + id).hide();
                $('#enable_expressionInterest_grid' + id).show();
                $('#message_success>div.header').html(response.message);
                $('#message_success').show();
                window.location.reload();
                setTimeout(function () {
                    $('#message_success').hide();
                }, 4000);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                $('#message_loading').hide();
            }
        });
    });
}