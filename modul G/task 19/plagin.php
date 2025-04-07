<?php
/*
Plugin Name: Обратный отсчет до 1 сентября
Description: Отображает время, оставшееся до 1 сентября в виджете.
Version: 1.0
Author: Your Name
*/

// Создаем виджет
class September_Countdown_Widget extends WP_Widget {

    function __construct() {
        parent::__construct(
            'september_countdown_widget', // Base ID
            __('Обратный отсчет до 1 сентября', 'september_countdown'), // Name
            array( 'description' => __('Отображает время, оставшееся до 1 сентября.', 'september_countdown'), ) // Args
        );
    }

    public function widget( $args, $instance ) {
        $title = apply_filters( 'widget_title', $instance['title'] );

        echo $args['before_widget'];
        if ( ! empty( $title ) ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        // Получаем оставшееся время
        $time_remaining = september_countdown_get_time_remaining();

        // Выводим оставшееся время в позиционированном div
        echo '<div style="position: fixed; bottom: 10px; left: 10px; background-color: rgba(255, 255, 255, 0.8); padding: 5px; border: 1px solid #ccc; z-index: 9999;">';
        echo __('До 1 сентября осталось:', 'september_countdown') . '<br>';
        echo $time_remaining;
        echo '</div>';

        echo $args['after_widget'];
    }

    public function form( $instance ) {
        $title = isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : __('Обратный отсчет', 'september_countdown');
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;
    }
}

// Функция для вычисления оставшегося времени
function september_countdown_get_time_remaining() {
    $september_1st = strtotime(date('Y') . '-09-01 00:00:00'); // 1 сентября текущего года

    // Если 1 сентября уже прошло, устанавливаем на 1 сентября следующего года
    if (time() > $september_1st) {
        $september_1st = strtotime((date('Y') + 1) . '-09-01 00:00:00');
    }

    $time_difference = $september_1st - time();

    $days = floor($time_difference / (60 * 60 * 24));
    $hours = floor(($time_difference % (60 * 60 * 24)) / (60 * 60));
    $minutes = floor(($time_difference % (60 * 60)) / 60);
    $seconds = floor($time_difference % 60);

    return sprintf('%02d:%02d:%02d:%02d', $days, $hours, $minutes, $seconds);
}


// Регистрируем виджет
function september_countdown_register_widget() {
    register_widget( 'September_Countdown_Widget' );
}
add_action( 'widgets_init', 'september_countdown_register_widget' );

// Подключаем стили (необязательно, но может быть полезно для кастомизации)
function september_countdown_enqueue_styles() {
    wp_enqueue_style( 'september-countdown-style', plugin_dir_url( __FILE__ ) . 'css/style.css' );
}
add_action( 'wp_enqueue_scripts', 'september_countdown_enqueue_styles' );

// Создаем папку css и добавляем пустой файл style.css для стилизации

?>
