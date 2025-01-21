jQuery(document).ready(function($) {
    // Variables para los modales y mensajes
    const modal = $('#field-modal');
    const form = modal.find('form');
    const loadingOverlay = $('<div class="jcf-loading">Loading...</div>').hide();
    $('body').append(loadingOverlay);

    // Abrir modal para nuevo campo
    $('.add-field-button').click(function(e) {
        e.preventDefault();
        resetModal();
        modal.show();
    });

    // Editar campo existente
    $('.edit-field').click(function(e) {
        e.preventDefault();
        const fieldId = $(this).data('id');
        loadingOverlay.show();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'jcf_get_field',
                nonce: jcf_admin.nonce,
                field_id: fieldId
            },
            success: function(response) {
                loadingOverlay.hide();
                if (response.success) {
                    fillModalForm(response.data);
                    modal.show();
                } else {
                    alert(response.data || jcf_admin.strings.error_loading);
                }
            },
            error: function() {
                loadingOverlay.hide();
                alert(jcf_admin.strings.error_loading);
            }
        });
    });

    // Eliminar campo
    $('.delete-field').click(function(e) {
        e.preventDefault();
        if (!confirm(jcf_admin.strings.confirm_delete)) {
            return;
        }

        const fieldId = $(this).data('id');
        const row = $(this).closest('tr');
        loadingOverlay.show();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'jcf_delete_field',
                nonce: jcf_admin.nonce,
                field_id: fieldId
            },
            success: function(response) {
                loadingOverlay.hide();
                if (response.success) {
                    row.fadeOut(400, function() {
                        row.remove();
                        if ($('tbody tr').length === 0) {
                            location.reload();
                        }
                    });
                } else {
                    alert(response.data || jcf_admin.strings.error_deleting);
                }
            },
            error: function() {
                loadingOverlay.hide();
                alert(jcf_admin.strings.error_deleting);
            }
        });
    });

    // Cerrar modal
    $('.jcf-modal-close').click(function() {
        modal.hide();
    });

    // Cerrar modal al hacer clic fuera
    $(window).click(function(e) {
        if ($(e.target).hasClass('jcf-modal')) {
            modal.hide();
        }
    });

    // Generar slug automáticamente
    $('#field_title').on('keyup', function() {
        if (!form.find('input[name="field_id"]').val()) {
            const slug = $(this)
                .val()
                .toLowerCase()
                .replace(/ /g, '-')
                .replace(/[^\w-]+/g, '');
            $('#field_slug').val(slug);
        }
    });

    // Manejar visibilidad de opciones adicionales según el tipo de campo
    $('#field_type').on('change', function() {
        const type = $(this).val();
        $('.field-options, .repeater-options').hide();

        if (type === 'select') {
            $('.field-options').show();
        } else if (type === 'repeater') {
            $('.repeater-options').show();
        }
    });

    $('.add-option').click(function(e) {
        e.preventDefault();
        const newRow = `
        <div class="option-row">
            <input type="text" 
                   name="field_options[values][]" 
                   placeholder="Valor" 
                   class="option-value">
            <input type="text" 
                   name="field_options[labels][]" 
                   placeholder="Etiqueta" 
                   class="option-label">
            <span class="remove-option dashicons dashicons-no-alt"></span>
        </div>
    `;
        $('.options-container').append(newRow);
    });

    $(document).on('click', '.remove-option', function() {
        $(this).closest('.option-row').remove();
    });

    // Funciones auxiliares
    function resetModal() {
        form[0].reset();
        form.find('input[name="field_id"]').val('');
        $('#field_slug').prop('readonly', false);
        $('.field-options').hide();
    }

    function fillModalForm(field) {
        form.find('input[name="field_id"]').val(field.id);
        form.find('input[name="field_title"]').val(field.title);
        form.find('input[name="field_slug"]').val(field.slug);
        form.find('select[name="field_type"]').val(field.type);
        form.find('textarea[name="field_description"]').val(field.description);

        // Limpiar checkboxes existentes
        form.find('input[type="checkbox"]').prop('checked', false);

        // Marcar condiciones seleccionadas
        if (field.conditions) {
            const conditions = JSON.parse(field.conditions);
            if (conditions.post_types) {
                conditions.post_types.forEach(function(type) {
                    form.find('input[name="field_conditions[post_types][]"][value="' + type + '"]')
                        .prop('checked', true);
                });
            }
            if (conditions.taxonomies) {
                conditions.taxonomies.forEach(function(tax) {
                    form.find('input[name="field_conditions[taxonomies][]"][value="' + tax + '"]')
                        .prop('checked', true);
                });
            }
        }

        if (field.type === 'select' && field.options) {
            const options = JSON.parse(field.options);
            $('.options-container').empty();

            for (const [value, label] of Object.entries(options)) {
                const optionRow = `
                <div class="option-row">
                    <input type="text" 
                           name="field_options[values][]" 
                           value="${value}" 
                           class="option-value">
                    <input type="text" 
                           name="field_options[labels][]" 
                           value="${label}" 
                           class="option-label">
                    <span class="remove-option dashicons dashicons-no-alt"></span>
                </div>
            `;
                $('.options-container').append(optionRow);
            }
        }

        // Bloquear edición del slug
        $('#field_slug').prop('readonly', true);

        // Mostrar/ocultar opciones según el tipo
        $('#field_type').trigger('change');
    }

    $(document).on('click', '.add-repeater-item', function(e) {
        e.preventDefault();
        const $repeater = $(this).closest('.jcf-repeater-field');
        const type = $repeater.data('type');
        const $items = $repeater.find('.jcf-repeater-items');

        let newItem = '<div class="repeater-item">';
        newItem += '<div class="repeater-item-content">';

        switch (type) {
            case 'text':
                newItem += '<input type="text" class="widefat repeater-input" value="" />';
                break;
            case 'textarea':
                newItem += '<textarea class="widefat repeater-input"></textarea>';
                break;
        }

        newItem += '</div>';
        newItem += '<button type="button" class="button remove-repeater-item">&times;</button>';
        newItem += '</div>';

        $items.append(newItem);
        updateRepeaterValue($repeater);
    });

    $(document).on('click', '.remove-repeater-item', function(e) {
        e.preventDefault();
        const $item = $(this).closest('.repeater-item');
        const $repeater = $item.closest('.jcf-repeater-field');
        $item.remove();
        updateRepeaterValue($repeater);
    });

    $(document).on('input', '.repeater-input', function() {
        const $repeater = $(this).closest('.jcf-repeater-field');
        updateRepeaterValue($repeater);
    });

    function updateRepeaterValue($repeater) {
        const values = [];
        $repeater.find('.repeater-input').each(function() {
            values.push($(this).val());
        });
        $repeater.find('.repeater-value').val(JSON.stringify(values));
    }
});
