<?php
class JCF_Field_Color extends JCF_Field_Base {
    protected function render_input() {
        $attrs = $this->build_attributes(array(
            'type' => 'color',
            'value' => empty($this->value) ? '#000000' : $this->value
        ));

        return sprintf(
            '<div class="jcf-color-field-wrapper">
                <input %s />
                <code class="jcf-color-value">%s</code>
            </div>',
            $attrs,
            esc_html(empty($this->value) ? '#000000' : $this->value)
        );
    }
}