<?php
// Gère toutes les fonctions de connexions
class Controller_Connexion extends Controller{
    // Affiche la page de connexion
    public function connexion(){        
        $myView = new View('connexion');
        $titre['titre'] = 'Connexion';
        $myView->render($titre);
    }
    // Vérifie les données du formulaire qui se trouve dans la page de connexion
    public function connexion_ctrl($data){
        $session = new Session;
        $validation = new Validation($data);
        $validation->cleaning()
                    ->required('login/pseudo ou email', 'mdp/mot de passe');
        $errors = $validation->getErrors();
        $data = $validation->getData();

        if (isset($_SESSION['reload'])) unset($_SESSION['reload']);
        if (!empty($errors)) {         
            $session->setFlash($errors);      
            $_SESSION['reload'] = $data;
            $session->render("connexion");
        }

        $user  = new model_utilisateur;

        // recherche l'utilisateur avec son login
        $reponse = $user->find(
            array(
                'pseudo' =>  $data['pseudo'],
            )
        );

        if (empty($reponse)) {       
            //recherche l'utilisateur avec son email               
            $reponse = $user->find(
                array(
                    'mail' => $data['pseudo'],
                )
            );
            if (empty($reponse)) {
                $session->setFlash('Le login ou l\'email n\'existent pas');
                $_SESSION['reload'] = $data;
                $session->render('connexion');
            }
        }

        $reponse = $reponse[0];
        $hashed = $reponse->mdp;

        if (password_verify($data['mdp'], $hashed)) {
            $_SESSION['pseudo']    = $reponse->pseudo;
            $_SESSION['niveau']   = $reponse->niveau;
            $_SESSION['nom']      = $reponse->nom;
            $_SESSION['prenom']   = $reponse->prenom;
            $_SESSION['mail']     = $reponse->mail;

            $session->setFlash('OK, vous êtes bien connecté', 'success');

            $session->render("accueil");
        } else {
            $session->setFlash("Aucun login/email ou mot de passe ne correspondent."); 
            $_SESSION['reload'] = $data;
            $session->render("connexion"); 
        }

        $myView = new View('accueil');
        $myView->render();
    }
    // Supprime les variables de session pour déconnecter l'utilisateur, affiche un message et retourne à l'accueil
    public function deconnexion(){
        $session = new Session();
        session_destroy();
        $session = new Session();
        $session->setFlash("Vous êtes bien déconnecté", 'success');
        Session::checkLogin();
        $session->render('accueil');
    }
}