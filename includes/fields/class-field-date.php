<?php
class JCF_Field_Date extends JCF_Field_Base {
    protected function render_input() {
        // Asegurarnos de que la fecha esté en formato Y-m-d para el input
        $formatted_value = '';
        if (!empty($this->value)) {
            $date = DateTime::createFromFormat('Y-m-d', $this->value);
            if ($date) {
                $formatted_value = $date->format('Y-m-d');
            }
        }

        $attrs = $this->build_attributes(array(
            'type' => 'date',
            'value' => $formatted_value,
            'pattern' => '\d{4}-\d{2}-\d{2}'
        ));

        return sprintf('<input %s />', $attrs);
    }
}