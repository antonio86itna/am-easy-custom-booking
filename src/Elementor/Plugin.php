<?php
// phpcs:ignoreFile
namespace AMCB\Elementor;

class Plugin {
    public static function register_category( $elements_manager ) {
        $elements_manager->add_category( 'amcb', [
            'title' => __( 'AMCB (Totaliweb)', 'amcb' ),
            'icon'  => 'fa fa-plug',
        ] );
    }
    public static function register_widgets($widgets_manager){
        require_once __DIR__ . '/Widgets/Search.php';
        require_once __DIR__ . '/Widgets/Results.php';
        require_once __DIR__ . '/Widgets/Checkout.php';
        require_once __DIR__ . '/Widgets/Dashboard.php';
        require_once __DIR__ . '/Widgets/Rates.php';

        $widgets_manager->register( new \AMCB\Elementor\Widgets\Search() );
        $widgets_manager->register( new \AMCB\Elementor\Widgets\Results() );
        $widgets_manager->register( new \AMCB\Elementor\Widgets\Checkout() );
        $widgets_manager->register( new \AMCB\Elementor\Widgets\Dashboard() );
        $widgets_manager->register( new \AMCB\Elementor\Widgets\Rates() );
    }
}
