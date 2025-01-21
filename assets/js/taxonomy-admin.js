jQuery(document).ready(function($) {
    const modal = $('#taxonomy-modal');
    const form = modal.find('form');
    const loadingOverlay = $('.jcf-loading');

    // Abrir modal para nueva taxonomía
    $('.add-taxonomy-button').click(function(e) {
        e.preventDefault();
        resetModal();
        modal.show();
    });

    // Editar taxonomía existente
    $('.edit-taxonomy').click(function(e) {
        e.preventDefault();
        const taxonomyId = $(this).data('id');
        loadingOverlay.show();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'jcf_get_taxonomy',
                nonce: jcf_admin.nonce,
                taxonomy_id: taxonomyId
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

    // Eliminar taxonomía
    $('.delete-taxonomy').click(function(e) {
        e.preventDefault();
        if (!confirm(jcf_admin.strings.confirm_delete_taxonomy)) {
            return;
        }

        const taxonomyId = $(this).data('id');
        const row = $(this).closest('tr');
        loadingOverlay.show();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'jcf_delete_taxonomy',
                nonce: jcf_admin.nonce,
                taxonomy_id: taxonomyId
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

    // Generar slug automáticamente
    $('#taxonomy_name').on('keyup', function() {
        if (!form.find('input[name="taxonomy_id"]').val()) {
            const slug = $(this)
                .val()
                .toLowerCase()
                .replace(/ /g, '-')
                .replace(/[^\w-]+/g, '');
            $('#taxonomy_slug').val(slug);
        }
    });

    // Funciones auxiliares
    function resetModal() {
        form[0].reset();
        form.find('input[name="taxonomy_id"]').val('');
        $('#taxonomy_slug').prop('readonly', false);
    }

    function fillModalForm(taxonomy) {
        form.find('input[name="taxonomy_id"]').val(taxonomy.id);
        form.find('input[name="taxonomy_name"]').val(taxonomy.name);
        form.find('input[name="taxonomy_slug"]').val(taxonomy.slug);
        form.find('textarea[name="taxonomy_description"]').val(taxonomy.description);
        form.find('input[name="taxonomy_hierarchical"]').prop('checked', taxonomy.hierarchical == 1);

        // Limpiar checkboxes existentes
        form.find('input[type="checkbox"][name="taxonomy_post_types[]"]').prop('checked', false);

        // Marcar post types seleccionados
        if (taxonomy.post_types) {
            const postTypes = JSON.parse(taxonomy.post_types);
            postTypes.forEach(function(type) {
                form.find('input[name="taxonomy_post_types[]"][value="' + type + '"]')
                    .prop('checked', true);
            });
        }

        // Bloquear edición del slug
        $('#taxonomy_slug').prop('readonly', true);
    }

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

    // Validación del formulario
    form.on('submit', function(e) {
        const postTypes = $('input[name="taxonomy_post_types[]"]:checked').length;
        if (postTypes === 0) {
            e.preventDefault();
            alert(jcf_admin.strings.select_post_type);
            return false;
        }
    });
});