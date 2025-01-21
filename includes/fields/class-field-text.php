<?php
class JCF_Field_Text extends JCF_Field_Base {
    protected function render_input() {
        $attrs = $this->build_attributes(array(
            'type' => 'text',
            'value' => $this->value
        ));

        return sprintf('<input %s />', $attrs);
    }
}