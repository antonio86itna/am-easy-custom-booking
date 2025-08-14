<?php
// phpcs:ignoreFile
namespace AMCB\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Checkout extends Widget_Base {
    public function get_name() { return 'amcb-checkout'; }
    public function get_title() { return __( 'AMCB – Checkout', 'amcb' ); }
    public function get_icon() { return 'eicon-cart'; }
    public function get_categories() { return [ 'amcb' ]; }
    protected function register_controls() {
        $this->start_controls_section( 'content', [ 'label' => __( 'Content', 'amcb' ) ] );
        $this->add_control( 'title', [ 'label' => __( 'Title', 'amcb' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Checkout', 'amcb' ) ] );
        $this->end_controls_section();
    }
    protected function render() {
        echo do_shortcode( '[amcb_checkout]' );
    }
}
