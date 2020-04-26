var PlgContato = PlgContato || {};
PlgContato.baseUrl = PlgContato.baseUrl || '';
PlgContato.ERROR_FALHA_EMAIL = PlgContato.PLG_CONTENT_LOAD_CONTATO_ERROR_FALHA_EMAIL || '';
PlgContato.ERROR_VALIDACAO_FORMULARIO = PlgContato.PLG_CONTENT_LOAD_CONTATO_ERROR_VALIDACAO_FORMULARIO || '';

(function ($) {
    $(document)
        .ready(function () {
            function getFormData($form) {
                var unindexed_array = $form.serializeArray();
                var indexed_array = {};
                $.map(unindexed_array, function (n, i) {
                    indexed_array[n['name']] = n['value'];
                });
                return indexed_array;
            }

            $('.md_contato_formulario_message').remove();

            $alertAux = $('<div class="md_contato_formulario_message alert alert-dismissible" role="alert">' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
                '<span class="message"></span>' +
                '</div>');

            $('.PLG_CONTENT_LOAD_CONTATO')
                .on('click', '.btn.btn-default.form__button', function (e) {
                    var $button = $(this).prop('disabled', true);

                    $('.md_contato_formulario_message').remove();
                    e.preventDefault();

                    $formulario = $(this).closest('form');
                    $formTitle = $formulario.find('.form__title');

                    if ($formulario[0].checkValidity()) {
                        var data = getFormData($(this).closest('form'));
                        $.post(PlgContato.baseUrl+"/index.php",
                            $.extend(data, {
                                option: 'com_ajax',
                                plugin: 'loadcontato',
                                group: 'content',
                                op: 'mailSender',
                                format: 'json'
                            }))
                            .done(function (response) {
                                $button.prop('disabled', false);
                                var $alert = $alertAux.clone();
                                $alert.addClass(response.success === true ? 'alert-success' : 'alert-danger')
                                if (response.success == true) {
                                    $alert.addClass('alert-success')
                                        .find('.message').text(response.data[0].message);
                                } else {
                                    $alert.addClass('alert-danger')
                                        .find('.message').text(response.message);
                                }
                                $formTitle.after($alert);
                            })
                            .fail(function () {
                                $button.prop('disabled', false);
                                var $alert = $alertAux.clone();
                                $formTitle.after(
                                    $alert.find('.message')
                                        .text(PlgContato.ERROR_FALHA_EMAIL)
                                        .end()
                                        .addClass('alert-danger')
                                );
                            });
                    } else {
                        $button.prop('disabled', false);
                        var $alert = $alertAux.clone();
                        $alert.addClass('alert-danger')
                            .find('.message').text(PlgContato.ERROR_VALIDACAO_FORMULARIO);
                        $formTitle.after($alert);
                    }
                    return false;
                });
        });
})(jQuery);