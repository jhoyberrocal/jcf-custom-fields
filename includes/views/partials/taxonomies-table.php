<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="jcf-taxonomies-list">
    <?php if (empty($taxonomies)): ?>
        <div class="jcf-no-items">
            <p><?php esc_html_e('No hay taxonomías personalizadas creadas todavía.', 'jhoy-custom-fields'); ?></p>
            <a href="#" class="button button-primary add-taxonomy-button">
                <?php esc_html_e('Crear la primera taxonomía', 'jhoy-custom-fields'); ?>
            </a>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
            <tr>
                <th scope="col" class="column-title">
                    <?php esc_html_e('Nombre', 'jhoy-custom-fields'); ?>
                </th>
                <th scope="col" class="column-slug">
                    <?php esc_html_e('Slug', 'jhoy-custom-fields'); ?>
                </th>
                <th scope="col" class="column-post-types">
                    <?php esc_html_e('Tipos de Contenido', 'jhoy-custom-fields'); ?>
                </th>
                <th scope="col" class="column-hierarchical">
                    <?php esc_html_e('Jerárquica', 'jhoy-custom-fields'); ?>
                </th>
                <th scope="col" class="column-actions">
                    <?php esc_html_e('Acciones', 'jhoy-custom-fields'); ?>
                </th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($taxonomies as $taxonomy): ?>
                <tr>
                    <td class="column-title">
                        <strong>
                            <a href="#" class="row-title edit-taxonomy" data-id="<?php echo esc_attr($taxonomy->id); ?>">
                                <?php echo esc_html($taxonomy->name); ?>
                            </a>
                        </strong>
                    </td>
                    <td class="column-slug">
                        <code><?php echo esc_html($taxonomy->slug); ?></code>
                    </td>
                    <td class="column-post-types">
                        <?php
                        $post_types = !empty($taxonomy->post_types) ? json_decode($taxonomy->post_types, true) : array();
                        $post_type_labels = array_map(function($pt) {
                            $type = get_post_type_object($pt);
                            return $type ? $type->labels->singular_name : $pt;
                        }, $post_types);
                        echo esc_html(implode(', ', $post_type_labels));
                        ?>
                    </td>
                    <td class="column-hierarchical">
                        <?php echo $taxonomy->hierarchical ?
                            esc_html__('Sí', 'jhoy-custom-fields') :
                            esc_html__('No', 'jhoy-custom-fields'); ?>
                    </td>
                    <td class="column-actions">
                        <div class="row-actions">
                            <span class="edit">
                                <a href="#" class="edit-taxonomy" data-id="<?php echo esc_attr($taxonomy->id); ?>">
                                    <?php esc_html_e('Editar', 'jhoy-custom-fields'); ?>
                                </a> |
                            </span>
                            <span class="delete">
                                <a href="#" class="delete-taxonomy" data-id="<?php echo esc_attr($taxonomy->id); ?>">
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
