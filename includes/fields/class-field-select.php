<?php
class JCF_Field_Select extends JCF_Field_Base {
    protected function render_input() {
        $attrs = $this->build_attributes();
        $output = sprintf('<select %s>', $attrs);

        if (!empty($this->field['options'])) {
            foreach ($this->field['options'] as $option_value => $option_label) {
                $output .= sprintf(
                    '<option value="%s"%s>%s</option>',
                    esc_attr($option_value),
                    selected($this->value, $option_value, false),
                    esc_html($option_label)
                );
            }
        }

        $output .= '</select>';
        return $output;
    }
}