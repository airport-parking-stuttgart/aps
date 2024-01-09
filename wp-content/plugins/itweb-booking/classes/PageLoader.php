<?php
class PageLoader {
    static function load(String $folder, String $action){
        echo '<link rel="stylesheet" href="'. plugin_dir_url(__FILE__) .'css\admin.css">';
        echo '<script src="'. plugin_dir_url(__FILE__) .'js\admin.js"></script>';
        if(isset($_GET[$action])){
            require_once plugin_dir_path(__FILE__) .'templates\\'. $folder .'\\' . $_GET[$action] . '.php';
        }
    }
}
