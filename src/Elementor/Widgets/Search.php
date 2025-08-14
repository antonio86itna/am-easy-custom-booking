<?php
// phpcs:ignoreFile
namespace AMCB\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class Search extends Widget_Base {
    public function get_name(){ return 'amcb-search'; }
    public function get_title(){ return 'AMCB – Search'; }
    public function get_icon(){ return 'eicon-search'; }
    public function get_categories(){ return ['amcb']; }
    protected function register_controls() {
        $this->start_controls_section('content', ['label'=>'Content']);
        $this->add_control('title', ['label'=>'Title','type'=>Controls_Manager::TEXT,'default'=>"Noleggia un'auto o scooter a Ischia"]);
        $this->end_controls_section();
    }
    protected function render() {
        echo do_shortcode('[amcb_search]');
    }
}
