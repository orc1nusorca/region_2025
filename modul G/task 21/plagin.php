<?php
/**
 * Plugin Name: Статус технических работ
 * Description: Позволяет переключать сайт в режим технических работ.
 * Version: 1.0
 * Author: Your Name
 */

// Добавляем пункт меню в Dashboard
add_action('admin_menu', 'maintenance_mode_menu');

function maintenance_mode_menu() {
    add_dashboard_page(
        'Статус технических работ',        // Page title
        'Статус технических работ',        // Menu title
        'manage_options',               // Capability required
        'maintenance-mode',             // Menu slug
        'maintenance_mode_page'         // Callback function
    );
}

// Callback функция для страницы статуса технических работ
function maintenance_mode_page() {
    // Сохраняем статус технических работ, если форма была отправлена
    if (isset($_POST['maintenance_mode_status'])) {
        update_option('maintenance_mode_enabled', sanitize_text_field($_POST['maintenance_mode_status']));
        echo '<div class="notice notice-success is-dismissible"><p>Настройки сохранены.</p></div>';
    }

    // Получаем текущий статус технических работ
    $maintenance_mode_enabled = get_option('maintenance_mode_enabled', 'disabled');
    ?>
    <div class="wrap">
        <h1><?php _e('Статус технических работ', 'maintenance-mode'); ?></h1>

        <form method="post">
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Режим технических работ', 'maintenance-mode'); ?></th>
                    <td>
                        <select name="maintenance_mode_status">
                            <option value="enabled" <?php selected($maintenance_mode_enabled, 'enabled'); ?>><?php _e('Включен', 'maintenance-mode'); ?></option>
                            <option value="disabled" <?php selected($maintenance_mode_enabled, 'disabled'); ?>><?php _e('Выключен', 'maintenance-mode'); ?></option>
                        </select>
                        <p class="description"><?php _e('Включите режим технических работ, чтобы отображать страницу обслуживания для посетителей сайта.', 'maintenance-mode'); ?></p>
                    </td>
                </tr>
            </table>

            <?php submit_button('Сохранить изменения'); ?>
        </form>
    </div>
    <?php
}

// Функция для отображения страницы технических работ на клиентской части
add_action('template_redirect', 'maintenance_mode_redirect');

function maintenance_mode_redirect() {
    // Проверяем, включен ли режим технических работ и не является ли текущий пользователь администратором
    if (get_option('maintenance_mode_enabled') == 'enabled' && !current_user_can('manage_options') && !( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
        // Загружаем шаблон страницы технических работ (maintenance.php)
        if (file_exists(TEMPLATEPATH . '/maintenance.php')) {
            require_once(TEMPLATEPATH . '/maintenance.php');
        } else {
            // Если шаблон не найден, выводим простое сообщение
            wp_die(
                '<h1>Сайт на обслуживании</h1><p>Извините, сайт сейчас находится на обслуживании. Пожалуйста, зайдите позже.</p>',
                'Сайт на обслуживании',
                array('response' => 503)
            );
        }

        exit; // Важно: прекращаем дальнейшее выполнение скрипта
    }
}

// Загрузка языковых файлов (опционально, для перевода плагина)
add_action( 'plugins_loaded', 'maintenance_mode_load_textdomain' );
function maintenance_mode_load_textdomain() {
  load_plugin_textdomain( 'maintenance-mode', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

//  Добавление HTTP статуса 503 Service Unavailable
add_action( 'get_header', 'maintenance_mode_http_status' );
function maintenance_mode_http_status() {
    if (get_option('maintenance_mode_enabled') == 'enabled' && !current_user_can('manage_options') && !is_admin() ) {
        header( 'HTTP/1.1 503 Service Unavailable', true, 503 );
        header( 'Retry-After: 3600' ); // Retry in 1 hour
    }
}

?>
