<div id="field-modal" class="jcf-modal">
    <div class="jcf-modal-content">
        <span class="jcf-modal-close">&times;</span>
        <h2><?php esc_html_e('Campo Personalizado', 'jhoy-custom-fields'); ?></h2>

        <form method="post" action="">
            <?php wp_nonce_field('jcf_save_field', 'jcf_field_nonce'); ?>
            <input type="hidden" name="action" value="save_field">
            <input type="hidden" name="field_id" value="">

            <div class="jcf-field-row">
                <label for="field_title">
                    <?php esc_html_e('Título:', 'jhoy-custom-fields'); ?>
                    <span class="required">*</span>
                </label>
                <input type="text"
                       name="field_title"
                       id="field_title"
                       class="regular-text"
                       required
                       placeholder="<?php esc_attr_e('Ingresa el título del campo', 'jhoy-custom-fields'); ?>">
            </div>

            <div class="jcf-field-row">
                <label for="field_slug">
                    <?php esc_html_e('Slug:', 'jhoy-custom-fields'); ?>
                    <span class="required">*</span>
                </label>
                <input type="text"
                       name="field_slug"
                       id="field_slug"
                       class="regular-text"
                       required
                       pattern="[a-z0-9-]+"
                       placeholder="<?php esc_attr_e('identificador-unico', 'jhoy-custom-fields'); ?>">
                <p class="description">
                    <?php esc_html_e('Solo letras minúsculas, números y guiones', 'jhoy-custom-fields'); ?>
                </p>
            </div>

            <div class="jcf-field-row">
                <label for="field_type">
                    <?php esc_html_e('Tipo:', 'jhoy-custom-fields'); ?>
                    <span class="required">*</span>
                </label>
                <select name="field_type" id="field_type" required>
                    <option value=""><?php esc_html_e('Selecciona un tipo', 'jhoy-custom-fields'); ?></option>
                    <option value="text"><?php esc_html_e('Texto', 'jhoy-custom-fields'); ?></option>
                    <option value="textarea"><?php esc_html_e('Área de texto', 'jhoy-custom-fields'); ?></option>
                    <option value="number"><?php esc_html_e('Número', 'jhoy-custom-fields'); ?></option>
                    <option value="select"><?php esc_html_e('Selección', 'jhoy-custom-fields'); ?></option>
                    <option value="color"><?php esc_html_e('Color', 'jhoy-custom-fields'); ?></option>
                    <option value="date"><?php esc_html_e('Fecha', 'jhoy-custom-fields'); ?></option>
                    <option value="repeater"><?php esc_html_e('Repeater', 'jhoy-custom-fields'); ?></option>
                </select>
            </div>

            <div class="jcf-field-row repeater-options" style="display: none;">
                <label for="repeater_type">
                    <?php esc_html_e('Tipo de campo a repetir:', 'jhoy-custom-fields'); ?>
                    <span class="required">*</span>
                </label>
                <select name="field_options[repeater_type]" id="repeater_type" required>
                    <option value="text"><?php esc_html_e('Texto', 'jhoy-custom-fields'); ?></option>
                    <option value="textarea"><?php esc_html_e('Área de texto', 'jhoy-custom-fields'); ?></option>
                </select>
                <p class="description">
                    <?php esc_html_e('Selecciona el tipo de campo que se repetirá', 'jhoy-custom-fields'); ?>
                </p>
            </div>

            <div class="jcf-field-row field-options" style="display: none;">
                <label><?php esc_html_e('Opciones:', 'jhoy-custom-fields'); ?></label>
                <div class="options-container">
                    <div class="option-row">
                        <input type="text"
                               name="field_options[values][]"
                               placeholder="<?php esc_attr_e('Valor', 'jhoy-custom-fields'); ?>"
                               class="option-value">
                        <input type="text"
                               name="field_options[labels][]"
                               placeholder="<?php esc_attr_e('Etiqueta', 'jhoy-custom-fields'); ?>"
                               class="option-label">
                        <span class="remove-option dashicons dashicons-no-alt"></span>
                    </div>
                </div>
                <button type="button" class="button add-option">
                    <?php esc_html_e('Añadir Opción', 'jhoy-custom-fields'); ?>
                </button>
            </div>

            <div class="jcf-field-row">
                <label><?php esc_html_e('Mostrar en:', 'jhoy-custom-fields'); ?></label>
                <div class="field-conditions">
                    <h4><?php esc_html_e('Tipos de Contenido', 'jhoy-custom-fields'); ?></h4>
                    <?php
                    $post_types = get_post_types(['public' => true], 'objects');
                    foreach ($post_types as $post_type):
                        $name = esc_attr($post_type->name);
                        $label = esc_html($post_type->labels->name);
                        ?>
                        <label class="condition-checkbox">
                            <input type="checkbox"
                                   name="field_conditions[post_types][]"
                                   value="<?php echo $name; ?>">
                            <?php echo $label; ?>
                        </label>
                    <?php endforeach; ?>

                    <h4><?php esc_html_e('Taxonomías', 'jhoy-custom-fields'); ?></h4>
                    <?php
                    $taxonomies = get_taxonomies(['public' => true], 'objects');
                    foreach ($taxonomies as $taxonomy):
                        $name = esc_attr($taxonomy->name);
                        $label = esc_html($taxonomy->labels->name);
                        ?>
                        <label class="condition-checkbox">
                            <input type="checkbox"
                                   name="field_conditions[taxonomies][]"
                                   value="<?php echo $name; ?>">
                            <?php echo $label; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="jcf-field-row">
                <label for="field_description">
                    <?php esc_html_e('Descripción:', 'jhoy-custom-fields'); ?>
                </label>
                <textarea name="field_description"
                          id="field_description"
                          class="regular-text"
                          rows="3"
                          placeholder="<?php esc_attr_e('Describe el propósito de este campo', 'jhoy-custom-fields'); ?>"></textarea>
            </div>

            <div class="jcf-form-actions">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e('Guardar Campo', 'jhoy-custom-fields'); ?>
                </button>
                <button type="button" class="button jcf-modal-close">
                    <?php esc_html_e('Cancelar', 'jhoy-custom-fields'); ?>
                </button>
            </div>
        </form>
    </div>
</div>