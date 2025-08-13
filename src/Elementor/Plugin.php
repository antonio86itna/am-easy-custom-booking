<?php
namespace AMCB\Elementor;

class Plugin {
    public static function register_category( $elements_manager ) {
        $elements_manager->add_category( 'amcb', [
            'title' => 'AMCB (Totaliweb)',
            'icon'  => 'fa fa-plug',
        ] );
    }
    public static function register_widgets($widgets_manager){
        require_once __DIR__ . '/Widgets/Search.php';
        $widgets_manager->register( new \AMCB\Elementor\Widgets\Search() );
    }
}
