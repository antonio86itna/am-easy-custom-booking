<?php
namespace AMCB\Api;

class Rest {
    public static function register() {
        add_action('rest_api_init', function(){
            register_rest_route('amcb/v1','/ping',[
                'methods'=>'GET','callback'=>function(){ return ['ok'=>true]; },
                'permission_callback'=>'__return_true'
            ]);
        });
    }
}
