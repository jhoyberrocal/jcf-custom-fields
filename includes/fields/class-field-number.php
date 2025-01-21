<?php
class JCF_Field_Number extends JCF_Field_Base {
    protected function render_input() {
        $attrs = $this->build_attributes(array(
            'type' => 'number',
            'value' => $this->value
        ));

        return sprintf('<input %s />', $attrs);
    }
}