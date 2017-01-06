$(function () {
    $('#add_additive_btn').click(function () {
        $('#add_additive.ui.modal').modal('setting', {
            autofocus: false,
            inverted: true,
            closable: false
        });
        $('#add_additive.ui.modal').modal('show');
    });

    $('#submit_additive').click(function (e) {
        e.preventDefault();
        $('#message_error').hide();
        $('#message_success').hide();
        $('#error_name_message').hide();
        $('#add_additive_form.ui.form').submit();
    });
    $('#add_additive_form.ui.form')
            .form({
                fields: {
                    reference: {
                        identifier: 'reference',
                        rules: [
                            {
                                type: 'empty',
                                prompt: 'Veuillez saisir la reférence'
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
                    }
                    
                },
                inline: true,
                on: 'blur',
                onSuccess: function (event, fields) {
                    $.ajax({
                        type: 'post',
                        url: $('#add_additive_form.ui.form').attr('action'),
                        data: fields,
                        dataType: 'json',
                        beforeSend: function () {
                            $('#submit_additive').addClass('disabled');
                            $('#cancel_add_additive').addClass('disabled');
                            $('#add_additive_form.ui.form').addClass('loading');
                        },
                        statusCode: {
                            500: function (xhr) {
                                $('#server_error_message').show();
                            },
                            400: function (response, textStatus, jqXHR) {
                                console.log(response);
                                var myerrors = response.responseJSON;
                                if (myerrors.success === false) {
                                    $('#error_name_header').html("Echec de la validation");
                                    $('#error_name_list').html('<li>' + myerrors.message + '</li>');
                                    $('#error_name_message').show();
                                }

                            }
                        },
                        success: function (response, textStatus, jqXHR) {
                            if (response.code === 200) {
                                $('#cancel_add_additive').removeClass('disabled');
                                $('#submit_additive').removeClass('disabled');
                                $('#add_additive_form.ui.form').removeClass('loading');
                                $('#list_as_grid_content').prepend(response.additive_content_grid);
                                $('#list_as_table_content').prepend(response.additive_content_list);
                                $('.ui.dropdown').dropdown({
                                    on: 'hover'
                                });
                                $('#add_additive.ui.modal').modal('hide');
                                $('#message_success>div.header').html('Additive ajouté avec succès !');
                                $('#message_success').show();
                                setTimeout(function () {
                                    $('#message_success').hide();
                                }, 4000);
                            }

                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            $('#cancel_add_additive').removeClass('disabled');
                            $('#submit_additive').removeClass('disabled');
                            $('#add_additive_form.ui.form').removeClass('loading');
                            /*alertify.error("Internal Server Error");*/
                        }
                    });
                    return false;
                }
            }
            );
});

function edit_additive(id) {
    $('#message_error').hide();
    $('#message_success').hide();
    $.ajax({
        type: 'PUT',
        url: '/additives/' + id,
        dataType: 'json',
        beforeSend: function () {
            $('#message_loading').show();
        },
        statusCode: {
            500: function (xhr) {

            },
            400: function (response, textStatus, jqXHR) {
                $('#message_error>div.header').html(response.responseJSON.message);
                $('#message_error').show();
            }
        },
        success: function (response, textStatus, jqXHR) {
            if (response.code === 200) {
                $('#edit_additive').remove();
                $('#edit_additive_content').html(response.edit_additive_form);
                $('#edit_additive.ui.modal').modal('setting', {
                    autofocus: false,
                    inverted: true
                });
                $('#edit_additive.ui.modal').modal('show');
                execute_edit(id);
            }
            $('#message_loading').hide();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $('#message_loading').hide()();
            /*alertify.error("Internal Server Error");*/
        }
    });
}

function execute_edit(id) {
    $('#submit_edit_additive').click(function (e) {
        e.preventDefault();
        $('#message_error').hide();
        $('#message_success').hide();
        $('#error_name_message').hide();
        $('#edit_additive_form.ui.form').submit();
    });
    $('#edit_additive_form.ui.form')
            .form({
                fields: {
                    name: {
                        identifier: 'name',
                        rules: [
                            {
                                type: 'empty',
                                prompt: "Veuillez saisir le nom de l'additif"
                            }
                        ]
                    }

                },
                inline: true,
                on: 'blur',
                onSuccess: function (event, fields) {
                    $.ajax({
                        type: 'PUT',
                        url: $('#edit_additive_form.ui.form').attr('action'),
                        data: fields,
                        dataType: 'json',
                        beforeSend: function () {
                            $('#submit_edit_additive').addClass('disabled');
                            $('#cancel_edit_additive').addClass('disabled');
                            $('#edit_additive_form.ui.form').addClass('loading');
                            $('#cancel_details_additive').addClass('disabled');
                            $('#disable_additive').addClass('disabled');
                            $('#enable_additive').addClass('disabled');
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
                                }

                            }
                        },
                        success: function (response, textStatus, jqXHR) {
                            if (response.code === 200) {
                                $('#submit_edit_additive').removeClass('disabled');
                                $('#cancel_edit_additive').removeClass('disabled');
                                $('#edit_additive_form.ui.form').removeClass('loading');
                                $('#cancel_details_additive').removeClass('disabled');
                                $('#disable_additive').removeClass('disabled');
                                $('#enable_additive').removeClass('disabled');
                                $('#additive_grid' + id).html(response.additive_content_grid);
                                $('#additive_list' + id).html(response.additive_content_list);
                                $('.ui.dropdown').dropdown({
                                    on: 'hover'
                                });
                                $('#edit_additive.ui.modal').modal('hide');
                                $('#message_success>div.header').html('Additif modifié avec succès !');
                                $('#message_success').show();
                                setTimeout(function () {
                                    $('#message_success').hide();
                                }, 4000);
                                $('#edit_additive').remove();
                            }

                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            $('#submit_edit_additive').removeClass('disabled');
                            $('#cancel_edit_additive').removeClass('disabled');
                            $('#edit_additive_form.ui.form').removeClass('loading');
                            /*alertify.error("Internal Server Error");*/
                        }
                    });
                    return false;
                }
            }
            );
}

function delete_additive(id) {
    $('#message_error').hide();
    $('#message_success').hide();
    $.ajax({
        type: 'DELETE',
        url: '/additives/' + id,
        dataType: 'json',
        beforeSend: function () {
            $('#message_loading').show();
        },
        statusCode: {
            500: function (xhr) {
                $('#message_error>div.header').html("Erreur s'est produite au niveau du serveur");
                $('#message_error').show();
                setTimeout(function () {
                    $('#message_error').hide();
                }, 4000);
            },
            400: function (response, textStatus, jqXHR) {
                $('#message_error>div.header').html(response.responseJSON.message);
                $('#message_error').show();
                setTimeout(function () {
                    $('#message_error').hide();
                }, 4000);
            }
        },
        success: function (response, textStatus, jqXHR) {
            console.log(response);
            $('#additive_grid' + id).remove();
            $('#additive_list' + id).remove();
            $('#message_loading').hide();
            $('#message_success>div.header').html(response.message);
            $('#message_success').show();
            setTimeout(function () {
                $('#message_success').hide();
            }, 4000);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $('#message_loading').hide();
            /*alertify.error("Internal Server Error");*/
        }
    });
}

function show_additive(id) {
    $('#message_error').hide();
    $('#message_success').hide();
    $.ajax({
        type: 'GET',
        url: '/additives/' + id,
        dataType: 'json',
        beforeSend: function () {
            $('#message_loading').show();
        },
        statusCode: {
            500: function (xhr) {

            },
            400: function (response, textStatus, jqXHR) {
                $('#message_error>div.header').html(response.responseJSON.message);
                $('#message_error').show();
            }
        },
        success: function (response, textStatus, jqXHR) {
            if (response.code === 200) {
                $('#edit_additive').remove();
                $('#edit_additive_content').html(response.additive_details);
                $('#edit_additive.ui.modal').modal('setting', {
                    autofocus: false,
                    inverted: true
                });
                $('#edit_additive.ui.modal').modal('show');
                execute_edit(id);
                $('#edit_additive_btn').click(function () {
                    $('#block_details').hide();
                    $('#block_form_edit').show();
                    $('#cancel_edit_additive').show();
                    $('#submit_edit_additive').show();
                    $(this).hide();
                });
                $('#cancel_edit_additive').click(function () {
                    $('#block_details').show();
                    $('#block_form_edit').hide();
                    $('#edit_additive_btn').show();
                    $('#submit_edit_additive').hide();
                    $(this).hide();
                });
            }
            $('#message_loading').hide();
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $('#message_loading').hide()();
            /*alertify.error("Internal Server Error");*/
        }
    });
}

function enable_additive(id) {
    $('#message_error').hide();
    $('#message_success').hide();
    $('#edit_additive.ui.modal').modal('hide');
    $('#edit_additive').remove();
    $.ajax({
        type: 'PUT',
        url: '/additives/' + id,
        data: {'action': 'enable'},
        dataType: 'json',
        beforeSend: function () {
            $('#message_loading').show();
        },
        statusCode: {
            500: function (xhr) {
                $('#message_error>div.header').html("Erreur s'est produite au niveau du serveur");
                $('#message_error').show();
                setTimeout(function () {
                    $('#message_error').hide();
                }, 4000);
            },
            400: function (response, textStatus, jqXHR) {
                $('#message_error>div.header').html("Echec d'activation de l'additif");
                $('#message_error').show();
                setTimeout(function () {
                    $('#message_error').hide();
                }, 4000);
            }
        },
        success: function (response, textStatus, jqXHR) {
            console.log(response);
            $('#message_loading').hide();
            $('#enable_additive_grid' + id).hide();
            $('#disable_additive_grid' + id).show();
            $('#message_success>div.header').html(response.message);
            $('#message_success').show();
            setTimeout(function () {
                $('#message_success').hide();
            }, 4000);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $('#message_loading').hide();
            /*alertify.error("Internal Server Error");*/
        }
    });
}

function disable_additive(id) {
    $('#message_error').hide();
    $('#message_success').hide();
    $('#edit_additive.ui.modal').modal('hide');
    $('#edit_additive').remove();
    $.ajax({
        type: 'PUT',
        url: '/additives/' + id,
        data: {'action': 'disable'},
        dataType: 'json',
        beforeSend: function () {
            $('#message_loading').show();
        },
        statusCode: {
            500: function (xhr) {
                $('#message_error>div.header').html("Erreur s'est produite au niveau du serveur");
                $('#message_error').show();
                setTimeout(function () {
                    $('#message_error').hide();
                }, 4000);
            },
            400: function (response, textStatus, jqXHR) {
                $('#message_error>div.header').html("Echec de la désactivation du additivee");
                $('#message_error').show();
                setTimeout(function () {
                    $('#message_error').hide();
                }, 4000);
            }
        },
        success: function (response, textStatus, jqXHR) {
            console.log(response);
            $('#message_loading').hide();
            $('#disable_additive_grid' + id).hide();
            $('#enable_additive_grid' + id).show();
            $('#message_success>div.header').html(response.message);
            $('#message_success').show();
            setTimeout(function () {
                $('#message_success').hide();
            }, 4000);
        },
        error: function (jqXHR, textStatus, errorThrown) {
            $('#message_loading').hide();
            /*alertify.error("Internal Server Error");*/
        }
    });
}