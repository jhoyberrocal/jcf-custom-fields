<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php echo esc_html__('Jhoy Custom Fields', 'jhoy-custom-fields'); ?>
    </h1>

    <?php if ($this->current_tab === 'fields'): ?>
        <a href="#" class="page-title-action add-field-button">
            <?php echo esc_html__('Añadir Nuevo Campo', 'jhoy-custom-fields'); ?>
        </a>
    <?php elseif ($this->current_tab === 'taxonomies'): ?>
        <a href="#" class="page-title-action add-taxonomy-button">
            <?php echo esc_html__('Añadir Nueva Taxonomía', 'jhoy-custom-fields'); ?>
        </a>
    <?php endif; ?>

    <?php if (isset($_GET['message'])): ?>
        <?php if ($_GET['message'] === 'saved'): ?>
            <div class="jcf-notice jcf-notice-success">
                <p><?php esc_html_e('Guardado exitosamente.', 'jhoy-custom-fields'); ?></p>
            </div>
        <?php elseif ($_GET['message'] === 'deleted'): ?>
            <div class="jcf-notice jcf-notice-success">
                <p><?php esc_html_e('Eliminado exitosamente.', 'jhoy-custom-fields'); ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="tab-container">
        <?php
        $tabs = array(
            'fields' => __('Campos', 'jhoy-custom-fields'),
            'taxonomies' => __('Taxonomías', 'jhoy-custom-fields')
        );
        ?>
        <nav class="nav-tab-wrapper">
            <?php foreach ($tabs as $tab_id => $tab_name): ?>
                <a href="?page=jhoy-custom-fields&tab=<?php echo esc_attr($tab_id); ?>"
                   class="nav-tab <?php echo $this->current_tab === $tab_id ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html($tab_name); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="tab-content">
            <?php
            switch ($this->current_tab):
                case 'taxonomies':
                    include JCF_PLUGIN_DIR . 'includes/views/partials/taxonomies-table.php';
                    include JCF_PLUGIN_DIR . 'includes/views/partials/taxonomy-modal.php';
                    break;

                default:
                    include JCF_PLUGIN_DIR . 'includes/views/partials/fields-table.php';
                    include JCF_PLUGIN_DIR . 'includes/views/partials/field-modal.php';
                    break;
            endswitch;
            ?>
        </div>

        <div class="image-motivation">
            <button type="button" class="page-title-action" onclick="showMotivation()">Show motivation</button>
        </div>

        <div class="image-motivation show-sydney" style="display: none">
            <img src="https://thefappeningblog.com/wp-content/uploads/2020/11/Sydney-Sweeney-Nude-Collage-thefappeningblog.com-1.jpg" alt="" />
            <img src="https://celebjihad.com/celeb-jihad/images/sydney_sweeney_nude_the_voyeurs.jpg" alt="">
            <img src="https://th.bing.com/th/id/R.063dc324653e76f053b4a216c8bc2237?rik=aAyY%2bs4RKgok7g&riu=http%3a%2f%2fthefappeningblog.com%2fwp-content%2fuploads%2f2019%2f07%2fSydney-Sweeney-Nude-TheFappeningBlog.com-1.jpg&ehk=vDA5srV70J1o1SOePMQSCQeZHWAhAtooQbEtmfiABEY%3d&risl=&pid=ImgRaw&r=0" alt="">
            <img src="https://thefappeningblog.com/wp-content/uploads/2022/01/sydney_sweeney_nude_euphoria_s02-thefappeningblog.com_.jpg" alt="">
        </div>

        <script>
            function showMotivation() {
                jQuery('.show-sydney').show();
            }
        </script>

        <style>
            .image-motivation {
                width: 100%;
                display: flex;
                flex-wrap: wrap;
                flex-direction: row;
                gap: 10px;
                justify-content: center;
                align-items: center;
                margin-top: 10px;
            }

            .image-motivation img {
                width: 500px;
                height: auto;
                object-fit: contain;
            }
        </style>
    </div>
</div>