<?php
/*
Plugin Name: My Simple Shop
Description: Простой интернет-магазин с возможностью добавления товаров и категорий.
Version: 1.0
Author: Ваше Имя
*/

// Защита от прямого доступа
if (!defined('ABSPATH')) {
    exit;
}

// Создание пользовательских таблиц при активации плагина
function mss_create_tables() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'mss_products';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        description text NOT NULL,
        price float NOT NULL,
        category_id mediumint(9) NOT NULL,
        image varchar(255) DEFAULT '' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    $table_name_categories = $wpdb->prefix . 'mss_categories';
    
    $sql .= "CREATE TABLE $table_name_categories (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'mss_create_tables');

// Добавление меню в админку
function mss_add_admin_menu() {
    add_menu_page('Магазин', 'Магазин', 'manage_options', 'mss_shop', 'mss_shop_page');
    add_submenu_page('mss_shop', 'Добавить товар', 'Добавить товар', 'manage_options', 'mss_add_product', 'mss_add_product_page');
    add_submenu_page('mss_shop', 'Добавить категорию', 'Добавить категорию', 'manage_options', 'mss_add_category', 'mss_add_category_page');
}
add_action('admin_menu', 'mss_add_admin_menu');

// Страница магазина
function mss_shop_page() {
    echo '<h1>Добро пожаловать в магазин!</h1>';
}

// Страница добавления товара
function mss_add_product_page() {
    global $wpdb;
    if ($_POST['submit']) {
        // Обработка формы добавления товара
        $name = sanitize_text_field($_POST['name']);
        $description = sanitize_textarea_field($_POST['description']);
        $price = floatval($_POST['price']);
        $category_id = intval($_POST['category_id']);
        
        // Загрузка изображения
        if (!empty($_FILES['image']['name'])) {
            $upload = wp_upload_bits($_FILES['image']['name'], null, file_get_contents($_FILES['image']['tmp_name']));
            if (isset($upload['error']) && $upload['error'] != 0) {
                echo "Ошибка загрузки изображения.";
            } else {
                $image = $upload['url'];
            }
        }

        // Вставка товара в базу данных
        $wpdb->insert($wpdb->prefix . 'mss_products', [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'category_id' => $category_id,
            'image' => isset($image) ? $image : ''
        ]);
        
        echo '<div class="updated"><p>Товар добавлен!</p></div>';
    }

    // Получение категорий для выпадающего списка
    $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mss_categories");

    ?>
    <h1>Добавить товар</h1>
    <form method="post" enctype="multipart/form-data">
        <label>Название товара:</label>
        <input type="text" name="name" required><br>
        
        <label>Описание:</label>
        <textarea name="description" required></textarea><br>
        
        <label>Цена:</label>
        <input type="number" name="price" step="0.01" required><br>
        
        <label>Категория:</label>
        <select name="category_id" required>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category->id; ?>"><?php echo $category->name; ?></option>
            <?php endforeach; ?>
        </select><br>
        
        <label>Изображение:</label>
        <input type="file" name="image" accept="image/*"><br>
        
        <input type="submit" name="submit" value="Добавить товар">
    </form>
    <?php
}

// Страница добавления категории
function mss_add_category_page() {
    global $wpdb;
    
    if ($_POST['submit']) {
        // Обработка формы добавления категории
        $name = sanitize_text_field($_POST['name']);
        $wpdb->insert($wpdb->prefix . 'mss_categories', ['name' => $name]);
        echo '<div class="updated"><p>Категория добавлена!</p></div>';
    }

    // Получение категорий для списка
    $categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mss_categories");
    
    ?>
    <h1>Добавить категорию</h1>
    <form method="post">
        <label>Название категории:</label>
        <input type="text" name="name" required><br>
        <input type="submit" name="submit" value="Добавить категорию">
    </form>

    <h2>Список категорий</h2>
    <ul>
        <?php foreach ($categories as $category): ?>
            <li><?php echo $category->name; ?></li>
        <?php endforeach; ?>
    </ul>
    <?php
}

// Вывод товаров на главной странице магазина (можно добавить отдельную страницу)
function mss_display_products() {
    global $wpdb;
    $products = $wpdb->get_results("SELECT p.*, c.name as category_name FROM {$wpdb->prefix}mss_products p JOIN {$wpdb->prefix}mss_categories c ON p.category_id = c.id");

    echo '<h1>Товары</h1>';
    foreach ($products as $product) {
        echo '<div class="product">';
        echo '<h2>' . esc_html($product->name) . '</h2>';
        echo '<p>' . esc_html($product->description) . '</p>';
        echo '<p>Цена: ' . esc_html($product->price) . '₽</p>';
        echo '<p>Категория: ' . esc_html($product->category_name) . '</p>';
        echo '<button>Купить</button>';
        echo '</div>';
    }
}

// Для отображения товаров на фронтенде можно использовать шорткод
add_shortcode('display_products', 'mss_display_products');
