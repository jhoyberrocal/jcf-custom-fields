<div id="taxonomy-modal" class="jcf-modal">
    <div class="jcf-modal-content">
        <span class="jcf-modal-close">&times;</span>
        <h2><?php esc_html_e('Taxonomía Personalizada', 'jhoy-custom-fields'); ?></h2>

        <form method="post" action="">
            <?php wp_nonce_field('jcf_save_taxonomy', 'jcf_taxonomy_nonce'); ?>
            <input type="hidden" name="action" value="save_taxonomy">
            <input type="hidden" name="taxonomy_id" value="">

            <div class="jcf-field-row">
                <label for="taxonomy_name">
                    <?php esc_html_e('Nombre:', 'jhoy-custom-fields'); ?>
                    <span class="required">*</span>
                </label>
                <input type="text"
                       name="taxonomy_name"
                       id="taxonomy_name"
                       class="regular-text"
                       required
                       placeholder="<?php esc_attr_e('Nombre de la taxonomía', 'jhoy-custom-fields'); ?>">
            </div>

            <div class="jcf-field-row">
                <label for="taxonomy_slug">
                    <?php esc_html_e('Slug:', 'jhoy-custom-fields'); ?>
                    <span class="required">*</span>
                </label>
                <input type="text"
                       name="taxonomy_slug"
                       id="taxonomy_slug"
                       class="regular-text"
                       required
                       pattern="[a-z0-9-]+"
                       placeholder="<?php esc_attr_e('identificador-unico', 'jhoy-custom-fields'); ?>">
                <p class="description">
                    <?php esc_html_e('Solo letras minúsculas, números y guiones', 'jhoy-custom-fields'); ?>
                </p>
            </div>

            <div class="jcf-field-row">
                <label><?php esc_html_e('Tipos de Contenido:', 'jhoy-custom-fields'); ?></label>
                <div class="field-conditions">
                    <?php
                    $post_types = get_post_types(['public' => true], 'objects');
                    foreach ($post_types as $post_type):
                        $name = esc_attr($post_type->name);
                        $label = esc_html($post_type->labels->name);
                        ?>
                        <label class="condition-checkbox">
                            <input type="checkbox"
                                   name="taxonomy_post_types[]"
                                   value="<?php echo $name; ?>">
                            <?php echo $label; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="jcf-field-row">
                <label>
                    <input type="checkbox" name="taxonomy_hierarchical" value="1">
                    <?php esc_html_e('Jerárquica (como categorías)', 'jhoy-custom-fields'); ?>
                </label>
                <p class="description">
                    <?php esc_html_e('Permite crear taxonomías padre-hijo', 'jhoy-custom-fields'); ?>
                </p>
            </div>

            <div class="jcf-field-row">
                <label for="taxonomy_description">
                    <?php esc_html_e('Descripción:', 'jhoy-custom-fields'); ?>
                </label>
                <textarea name="taxonomy_description"
                          id="taxonomy_description"
                          class="regular-text"
                          rows="3"
                          placeholder="<?php esc_attr_e('Describe el propósito de esta taxonomía', 'jhoy-custom-fields'); ?>"></textarea>
            </div>

            <div class="jcf-form-actions">
                <button type="submit" class="button button-primary">
                    <?php esc_html_e('Guardar Taxonomía', 'jhoy-custom-fields'); ?>
                </button>
                <button type="button" class="button jcf-modal-close">
                    <?php esc_html_e('Cancelar', 'jhoy-custom-fields'); ?>
                </button>
            </div>
        </form>
    </div>
</div>