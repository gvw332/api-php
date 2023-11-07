<?php
// Gère toutes les fonctions de connexions
class Controller_Connexion extends Controller
{
    // Affiche la page de connexion
    public function connexion()
    {
        $myView = new View('connexion');
        $titre['titre'] = 'Connexion';
        $myView->render($titre);
    }
    // Vérifie les données du formulaire qui se trouve dans la page de connexion
    public function connexion_ctrl($data)
    {
        $validation = new Validation($data);
        $validation->cleaning()
            ->required('mail/email', 'mdp/mot de passe');
        $errors = $validation->getErrors();
        $data = $validation->getData();
    
        $user  = new model_utilisateur;
        
        // Recherche l'utilisateur avec son email
        $reponse = $user->find(
            array(
                'mail' => $data['mail'],
            )
        );
    
        $response_data = array();
    
        if (empty($reponse)) {
            $response_data['message'] = "Utilisateur non trouvé";
        } else {
            $hashed = $reponse['mdp'];
    
            if (password_verify($data['mdp'], $hashed)) {
                $response_data['message'] = "Connexion réussie";
                unset($reponse['mdp']);
                unset($reponse['id']);
                $response_data['user'] = $reponse;
            } else {
                $response_data['message'] = "Mot de passe incorrect";
            }
        }
        // var_dump($response_data);
        // die;
        echo json_encode($response_data);
    }
}
