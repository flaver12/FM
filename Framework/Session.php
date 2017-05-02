<?php
namespace FM\Framework;

class Session {

    public static function start() {
        session_start();
    }

    public static function destroy() {
        session_destroy();
    }

    public static function set($name, $value) {
        $_SESSION['fm'][$name] = $value;
    }

    public static function get($name) {
        if(isset($_SESSION['fm'][$name]))
            return $_SESSION['fm'][$name];
        else
            return false;
    }

    public static function exist($name) {
        if(isset($_SESSION['fm'][$name]))
            return true;
        else
            return false;
    }

    public static function delete($name) {
        if(isset($_SESSION['fm'][$name]))
            unset($_SESSION['fm'][$name]);
        else
            return false;
    }

}