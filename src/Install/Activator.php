<?php
namespace AMCB\Install;

class Activator {
    public static function activate() { /* create tables later via dbDelta or migrations */ }
    public static function deactivate() { /* cleanup scheduled events if any */ }
}
