<?php
class JCF_Field_Repeater extends JCF_Field_Base {
    private function get_field_class($type) {
        $field_classes = array(
            'text' => 'JCF_Field_Text',
            'textarea' => 'JCF_Field_Textarea',
            'select' => 'JCF_Field_Select',
            'number' => 'JCF_Field_Number',
            'color' => 'JCF_Field_Color',
            'date' => 'JCF_Field_Date'
        );

        return isset($field_classes[$type]) ? $field_classes[$type] : 'JCF_Field_Text';
    }

    protected function render_input() {
        // Procesar el valor existente
        $values = [];
        if (!empty($this->value)) {
            if (is_string($this->value)) {
                $decoded = json_decode($this->value, true);
                if (is_array($decoded)) {
                    $values = $decoded;
                }
            } elseif (is_array($this->value)) {
                $values = $this->value;
            }
        }

        // Si no hay valores o están vacíos, inicializar con un campo vacío
        if (empty($values)) {
            $values = [''];
        }

        // Obtener el tipo de repeater de las opciones
        $options = !empty($this->field['options']) ? json_decode($this->field['options'], true) : [];
        $repeater_type = $options['repeater_type'] ?? 'text';

        $output = sprintf(
            '<div class="jcf-repeater-field" data-field-name="%s" data-type="%s">',
            esc_attr($this->field['slug']),
            esc_attr($repeater_type)
        );

        // Contenedor para los items repetibles
        $output .= '<div class="jcf-repeater-items">';

        // Renderizar cada item existente
        foreach ($values as $value) {
            $output .= $this->render_repeater_item($value, $repeater_type, $options);
        }

        $output .= '</div>';

        // Botón para añadir nuevos items
        $output .= sprintf(
            '<button type="button" class="button add-repeater-item">%s</button>',
            esc_html__('Añadir Item', 'jhoy-custom-fields')
        );

        // Input oculto para almacenar los valores
        $output .= sprintf(
            '<input type="hidden" name="jcf_%s" class="repeater-value" value="%s">',
            esc_attr($this->field['slug']),
            esc_attr(wp_json_encode($values))
        );

        return $output;
    }

    private function render_repeater_item($value, $type, $options) {
        $output = '<div class="repeater-item">';
        $output .= '<div class="repeater-item-content">';

        // Crear un campo temporal con la configuración necesaria
        $temp_field = array(
            'slug' => uniqid('temp_'), // Slug temporal único
            'type' => $type,
            'options' => $options,
            'title' => '', // No necesitamos título para el campo repetido
            'description' => '' // No necesitamos descripción para el campo repetido
        );

        // Obtener la clase del campo
        $field_class = $this->get_field_class($type);
        if (class_exists($field_class)) {
            // Instanciar la clase del campo
            $field_instance = new $field_class($temp_field, $value);

            // Obtener el HTML del input
            $field_html = $field_instance->render_input();

            // Modificar las clases y nombres de los campos para que funcionen en el repeater
            $field_html = preg_replace(
                '/class="([^"]*)"/',
                'class="$1 repeater-input"',
                $field_html,
                1
            );

            $field_html = preg_replace(
                '/name="([^"]*)"/',
                'name="temp_name"', // El nombre real se maneja via JavaScript
                $field_html,
                1
            );

            $output .= $field_html;
        }

        $output .= '</div>';
        $output .= '<button type="button" class="button remove-repeater-item">&times;</button>';
        $output .= '</div>';

        return $output;
    }
}