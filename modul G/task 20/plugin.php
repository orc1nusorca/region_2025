<?php
/**
 * Plugin Name: Загрузка галереи
 * Description: Позволяет загружать несколько медиафайлов в галерею WordPress через пункт меню "Загрузка галереи".
 * Version: 1.0
 * Author: Your Name
 */

// Добавляем пункт меню "Загрузка галереи"
add_action('admin_menu', 'gallery_upload_menu');

function gallery_upload_menu() {
    add_menu_page(
        'Загрузка галереи',         // Page title
        'Загрузка галереи',         // Menu title
        'upload_files',             // Capability required
        'gallery-upload',           // Menu slug
        'gallery_upload_page',      // Callback function
        'dashicons-format-gallery', // Icon (dashicons)
        25                          // Position in menu
    );
}

// Callback функция для страницы загрузки галереи
function gallery_upload_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Загрузка галереи', 'gallery-upload'); ?></h1>

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('gallery_upload_nonce', 'gallery_upload_nonce'); ?>
            <p>
                <label for="gallery_files"><?php _e('Выберите файлы для загрузки:', 'gallery-upload'); ?></label><br>
                <input type="file" name="gallery_files[]" id="gallery_files" multiple="multiple">
            </p>

            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Загрузить файлы', 'gallery-upload'); ?>">
            </p>
        </form>

        <?php
        // Обработка отправки формы
        if (isset($_POST['submit'])) {
            if (!isset($_POST['gallery_upload_nonce']) || !wp_verify_nonce($_POST['gallery_upload_nonce'], 'gallery_upload_nonce')) {
                _e('Ошибка безопасности!', 'gallery-upload');
                exit;
            }

            if (isset($_FILES['gallery_files']) && !empty($_FILES['gallery_files']['name'][0])) {
                $files = $_FILES['gallery_files'];

                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');

                foreach ($files['name'] as $key => $value) {
                    if ($files['error'][$key] == UPLOAD_ERR_OK) {
                        $file = array(
                            'name' => $files['name'][$key],
                            'type' => $files['type'][$key],
                            'tmp_name' => $files['tmp_name'][$key],
                            'error' => $files['error'][$key],
                            'size' => $files['size'][$key]
                        );

                        $attachment_id = media_handle_sideload($file, 0); // 0 for no post association

                        if (is_wp_error($attachment_id)) {
                            echo '<p style="color: red;">' . sprintf(__('Ошибка загрузки файла %s: %s', 'gallery-upload'), esc_html($file['name']), $attachment_id->get_error_message()) . '</p>';
                        } else {
                            echo '<p style="color: green;">' . sprintf(__('Файл %s успешно загружен.', 'gallery-upload'), esc_html($file['name'])) . '</p>';
                            // Optional:  Add attachment ID to a specific gallery or post
                            // For example, you could add it to the 'featured_images' custom field for a specific post.
                        }
                    } else {
                        echo '<p style="color: red;">' . sprintf(__('Ошибка при загрузке файла %s. Код ошибки: %d', 'gallery-upload'), esc_html($files['name'][$key]), $files['error'][$key]) . '</p>';
                    }
                }
            } else {
                _e('Пожалуйста, выберите файлы для загрузки.', 'gallery-upload');
            }
        }
        ?>
    </div>
    <?php
}