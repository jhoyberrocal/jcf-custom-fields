<?php
abstract class JCF_Field_Base {
    protected $field;
    protected $value;

    public function __construct($field, $value) {
        $this->field = $field;
        $this->value = $value;
    }

    abstract protected function render_input();

    public function render() {
        $output = $this->render_input();

        // Añadir descripción si existe
        if (!empty($this->field['description'])) {
            $output .= sprintf(
                '<p class="description">%s</p>',
                esc_html($this->field['description'])
            );
        }

        return $output;
    }

    protected function get_base_attributes() {
        return array(
            'id' => 'jcf_' . esc_attr($this->field['slug']),
            'name' => 'jcf_' . esc_attr($this->field['slug']),
            'class' => 'widefat'
        );
    }

    protected function build_attributes($additional_attrs = array()) {
        $attrs = array_merge($this->get_base_attributes(), $additional_attrs);
        $output = '';

        foreach ($attrs as $key => $value) {
            $output .= sprintf(' %s="%s"', esc_attr($key), esc_attr($value));
        }

        return $output;
    }
}
