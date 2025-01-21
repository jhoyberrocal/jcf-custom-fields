<?php
class JCF_Field_Textarea extends JCF_Field_Base {
    protected function render_input() {
        $attrs = $this->build_attributes();
        return sprintf(
            '<textarea %s>%s</textarea>',
            $attrs,
            esc_textarea($this->value)
        );
    }
}