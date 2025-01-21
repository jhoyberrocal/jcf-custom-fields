jQuery(document).ready(function($) {
    function updateRepeaterValue($repeater) {
        const values = [];
        $repeater.find('.repeater-input').each(function() {
            const $input = $(this);
            let value;

            // Obtener el valor según el tipo de input
            if ($input.is('textarea')) {
                value = $input.val();
            } else if ($input.is('select')) {
                value = $input.val();
            } else if ($input.attr('type') === 'color') {
                value = $input.val();
            } else if ($input.attr('type') === 'number') {
                value = $input.val();
            } else if ($input.attr('type') === 'date') {
                value = $input.val();
            } else {
                // Input text por defecto
                value = $input.val();
            }

            values.push(value);
        });

        $repeater.find('.repeater-value').val(JSON.stringify(values));
    }

    // Delegación de eventos para el botón de añadir
    $(document).on('click', '.add-repeater-item', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const $repeater = $(this).closest('.jcf-repeater-field');
        const $lastItem = $repeater.find('.repeater-item:last');

        if ($lastItem.length) {
            // Clonar el último item
            const $newItem = $lastItem.clone();

            // Limpiar valores
            $newItem.find('.repeater-input').each(function() {
                const $input = $(this);
                if ($input.is('select')) {
                    $input.prop('selectedIndex', 0);
                } else if ($input.attr('type') === 'color') {
                    $input.val('#000000');
                } else {
                    $input.val('');
                }
            });

            // Añadir el nuevo item
            $repeater.find('.jcf-repeater-items').append($newItem);
        }

        // Actualizar el valor del repeater
        updateRepeaterValue($repeater);
    });

    // Delegación de eventos para el botón de eliminar
    $(document).on('click', '.remove-repeater-item', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const $item = $(this).closest('.repeater-item');
        const $repeater = $item.closest('.jcf-repeater-field');

        // No eliminar si es el único item
        if ($repeater.find('.repeater-item').length > 1) {
            $item.fadeOut(300, function() {
                $(this).remove();
                updateRepeaterValue($repeater);
            });
        }
    });

    // Actualizar valor cuando cambia cualquier input
    $(document).on('input change', '.repeater-input', function() {
        const $repeater = $(this).closest('.jcf-repeater-field');
        updateRepeaterValue($repeater);
    });

    // Inicializar los valores al cargar la página
    $('.jcf-repeater-field').each(function() {
        updateRepeaterValue($(this));
    });
});