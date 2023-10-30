<?php

// Gère les fonctions communes des autres contrôleurs
class Controller{   
    // A l'affectation de la classe on crée une session et on vérifie si les utilisateurs sont connectés ou pas ou si c'est un admin
    public function __construtor(){
        $session = new Session();
        Session::checkLogin();
    }
    // Transforme un tableau en objet 
    function arrayToObj($array){
        $object = new stdClass();
        foreach ($array as $key => $value) {
            $object->$key = $value;
        }
        return $object;
    }
    // Transforme un objet en tableau 
    function objToArray($obj){
        $object = new stdClass();
        return (array)$obj;
    }
};
  