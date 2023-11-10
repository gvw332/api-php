<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Method: *");

// Récupère l'url transformée et crée une request exécuter le routeur
include_once('../core/config.php');

MyAutoload::start();

if (isset($_GET['r'])) {
    $request = $_GET['r'];
    $routeur = new Routeur($request);
    $routeur->renderController();
} else {
    echo "serveur a l'écoute";
}
