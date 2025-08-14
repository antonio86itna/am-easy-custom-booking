<?php
// phpcs:ignoreFile
namespace AMCB\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Results extends Widget_Base {
    public function get_name(){ return 'amcb-results'; }
    public function get_title(){ return 'AMCB – Results'; }
    public function get_icon(){ return 'eicon-post-list'; }
    public function get_categories(){ return ['amcb']; }
    protected function register_controls() {
        $this->start_controls_section('content', ['label'=>'Content']);
        $this->add_control('title', ['label'=>'Title','type'=>Controls_Manager::TEXT,'default'=>'Results']);
        $this->end_controls_section();
    }
    protected function render() {
        echo do_shortcode('[amcb_results]');
    }
}
