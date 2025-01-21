<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="jcf-fields-list">
    <?php if (empty($fields)): ?>
        <div class="jcf-no-items">
            <p><?php esc_html_e('No hay campos personalizados creados todavía.', 'jhoy-custom-fields'); ?></p>
            <a href="#" class="button button-primary add-field-button">
                <?php esc_html_e('Crear el primer campo', 'jhoy-custom-fields'); ?>
            </a>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
            <tr>
                <th scope="col" class="column-title">
                    <?php esc_html_e('Título', 'jhoy-custom-fields'); ?>
                </th>
                <th scope="col" class="column-slug">
                    <?php esc_html_e('Slug', 'jhoy-custom-fields'); ?>
                </th>
                <th scope="col" class="column-type">
                    <?php esc_html_e('Tipo', 'jhoy-custom-fields'); ?>
                </th>
                <th scope="col" class="column-location">
                    <?php esc_html_e('Ubicación', 'jhoy-custom-fields'); ?>
                </th>
                <th scope="col" class="column-actions">
                    <?php esc_html_e('Acciones', 'jhoy-custom-fields'); ?>
                </th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($fields as $field): ?>
                <tr>
                    <td class="column-title">
                        <strong>
                            <a href="#" class="row-title edit-field" data-id="<?php echo esc_attr($field->id); ?>">
                                <?php echo esc_html($field->title); ?>
                            </a>
                        </strong>
                    </td>
                    <td class="column-slug">
                        <code><?php echo esc_html($field->slug); ?></code>
                    </td>
                    <td class="column-type">
                        <?php
                        $types = array(
                            'text' => __('Texto', 'jhoy-custom-fields'),
                            'textarea' => __('Área de texto', 'jhoy-custom-fields'),
                            'number' => __('Número', 'jhoy-custom-fields'),
                            'select' => __('Selección', 'jhoy-custom-fields'),
                            'email' => __('Email', 'jhoy-custom-fields'),
                            'url' => __('URL', 'jhoy-custom-fields'),
                            'date' => __('Fecha', 'jhoy-custom-fields')
                        );
                        echo esc_html($types[$field->type] ?? $field->type);
                        ?>
                    </td>
                    <td class="column-location">
                        <?php
                        $conditions = !empty($field->conditions) ? json_decode($field->conditions, true) : array();
                        $locations = array();

                        if (!empty($conditions['post_types'])) {
                            $post_type_names = array_map(function($pt) {
                                $type = get_post_type_object($pt);
                                return $type ? $type->labels->singular_name : $pt;
                            }, $conditions['post_types']);
                            $locations[] = implode(', ', $post_type_names);
                        }

                        if (!empty($conditions['taxonomies'])) {
                            $tax_names = array_map(function($tax) {
                                $taxonomy = get_taxonomy($tax);
                                return $taxonomy ? $taxonomy->labels->singular_name : $tax;
                            }, $conditions['taxonomies']);
                            $locations[] = implode(', ', $tax_names);
                        }

                        echo esc_html(implode(' | ', $locations));
                        ?>
                    </td>
                    <td class="column-actions">
                        <div class="row-actions">
                            <span class="edit">
                                <a href="#" class="edit-field" data-id="<?php echo esc_attr($field->id); ?>">
                                    <?php esc_html_e('Editar', 'jhoy-custom-fields'); ?>
                                </a> |
                            </span>
                            <span class="delete">
                                <a href="#" class="delete-field" data-id="<?php echo esc_attr($field->id); ?>">
                                    <?php esc_html_e('Eliminar', 'jhoy-custom-fields'); ?>
                                </a>
                            </span>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>