<?php


// Permet de charger automatiquement les classes sans devoir faire d'include dans chaque fichier
class MyAutoload{
    // Configure l'environnement de l'application et définit les constantes pour les répertoires et les chemins d'accès.
    public static function start(){
        spl_autoload_register(array(__CLASS__, 'autoload'));

        $root = $_SERVER['DOCUMENT_ROOT'];
        $host = $_SERVER['HTTP_HOST'];

        if ($_SERVER['SERVER_NAME'] == 'localhost') {
            define('HOST', 'http://' . $host . '/api-php-react/');
            define('ROOT', $root . '/api-php-react/');
        } else {
            define('HOST', 'https://' . $host . '/');
            define('ROOT', '/' . $root . '/');
        }


        define('CONTROLLER', ROOT . 'controller/');
        define('VIEW', ROOT . 'view/');
        define('MODEL', ROOT . 'model/');
        define('CLASSES', ROOT . 'core/');

        
    }
    // Permet de charger dynamiquement les classes en incluant les fichiers correspondants en fonction de leur emplacement dans les répertoires modèle, classe, contrôleur, FPDF et PHPMailer.
    public static function autoload($class){
        if (file_exists(MODEL . $class . '.php')) {
            include_once(MODEL . $class . '.php');
        } else if (file_exists(CLASSES . $class . '.php')) {
            include_once(CLASSES . $class . '.php');
        } else if (file_exists(CONTROLLER . $class . '.php')) {
            include_once(CONTROLLER . $class . '.php');
        } 
    }
}


// PAGE DE CONNEXION A LA DB

if ($_SERVER['SERVER_NAME'] == 'localhost') {
    // en local
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'ecommerce');
} else {
    // sur le site
    define('DB_HOST', '');
    define('DB_USER', '');
    define('DB_PASS', '');
    define('DB_NAME', '');
}


//cle stripe
define('PK_STRIPE', 'pk_test_51O2YZFIzz2Dktgswd5bNSjalFuRXubc3By7h24thNwlEntQNM639JJZAdyGU438tCQUOzLU8jhCN93ZFidibkADf00QopYXYSM');
define('SK_STRIPE', 'sk_test_51O2YZFIzz2DktgswhZJVZc6EKXH0sDIEJhHscxVh6yB3Uchev48gsh3k2qfRIjsBPfvvEAxsgDaY2JtVM8Q0k23R00L7Php09P');