<?php
// Gère la mise en place des vues
class View{
    private $view;
    private $template;
    // Mise en mémoire dans l'instance de la vue et du template
    public function __construct($view = null, $template = '_template.php'){
        $this->view = $view;
        $this->template = $template;
    }
    // Récupère les paramètres associées aux vues
    public function render($params = array()){

        extract($params);
        $title = '';

        if (isset($params['titre'])) {
            $title = $params['titre'];
            unset($params['titre']);
        }
        $session = new Session();

        $view = $this->view;
        ob_start();

        
        include(VIEW . $view . '.php');

        $contentPage = ob_get_clean();

        include_once(VIEW . $this->template);
    }
    // Redirige vers une autre page
    public function redirect($route){
        header("Location: " . HOST . $route);
        exit;
    }
}