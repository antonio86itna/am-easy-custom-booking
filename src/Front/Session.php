<?php
namespace AMCB\Front;

class Session {
    const TTL = 900; // 15 min
    public static function id() {
        if (empty($_COOKIE['amcb_sid'])) {
            $sid = wp_generate_uuid4();
            setcookie('amcb_sid', $sid, time()+DAY_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
            $_COOKIE['amcb_sid'] = $sid;
        }
        return $_COOKIE['amcb_sid'];
    }
    public static function get($key=null) {
        $data = get_transient('amcb_sess_' . self::id()) ?: [];
        return $key ? ($data[$key] ?? null) : $data;
    }
    public static function set($key,$val) {
        $data = self::get(); $data[$key] = $val;
        set_transient('amcb_sess_' . self::id(), $data, self::TTL);
    }
    public static function destroy(){ delete_transient('amcb_sess_' . self::id()); }
}
