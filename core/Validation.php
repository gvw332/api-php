<?php
// Gère toutes les validations sur les formulaires quand ils ont été postés
class Validation{
    private $data;
    private $errors = '';

    // Récupère les données en paramètres et les met en mémoire
    public function __construct(array $data){
        $this->data = $data;
    }
    // Stipule les champs obligatoires
    public function required(string ...$keys): self{
        foreach ($keys as $key) {

            $res = explode('/', $key);
            $key = $res[0];
            $champ = ucfirst($key);
            if (isset($res[1])) {
                $champ = $res[1];
            }
            if (array_key_exists($key, $this->data)) {
                if (empty($this->data[$key])) {
                    $this->errors .= "Le champ [$champ] est vide<br>";
                   
                }
            }
            
            
        }
        return $this; // Permet d'enchaîner les méthodes
    }
    // Vérifie si c'est bien un email
    public function email(string ...$keys): self{
        foreach ($keys as $key) {
            $res = explode('/', $key);
            $key = $res[0];
            $champ = ucfirst($key);
            if (isset($res[1])) {
                $champ = $res[1];
            }
            if (array_key_exists($key, $this->data)) {
                if (strpos($this->errors, $champ) === false) {
                    if (!filter_var($this->data[$key], FILTER_VALIDATE_EMAIL)) {
                        $this->errors .= "Le champ [$champ] n'est pas valide<br> ";
                    }
                }
               
            }
           
        }

        return $this; // Permet d'enchaîner les méthodes
    }
    // Nettoie la valeur du champ et enleve les "" et les espaces
    public function cleaning(): self{
        foreach ($this->data as $key => $value) {
            
            if (!strstr($value, '<br>')) {
                $cleanValue = trim(strip_tags($value), '" ');
                $this->data[$key] = $cleanValue;
            }
        }
        return $this;
    }
    // Retourne les erreurs 
    function getErrors(): string{
        return $this->errors;
    }
    // Retourne les données validées
    function getData(): array{
        return $this->data;
    }
}