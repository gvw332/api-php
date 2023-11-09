<?php

// Gère toutes les fonctions liées à la table users
class Controller_Utilisateur extends Controller
{
    // Certaines fonctions auront pour but l'évolution communautaire du site telles que admin ,delete, update et save.
    // Gère la page admin
    public function admin()
    {
        $user  = new model_utilisateur;

        $myView = new View('admin');
        $users = $user->all();
        $users['titre'] = 'Admin';
        $myView->render($users);
    }
    // Supprime un utilisateur dans la base de données
    public function delete($id)
    {
        $id = $id['id'];

        // Suppression de l'utilisateur
        $user  = new model_utilisateur;
        $user->delete($id);

        // Retour à la page Admin
        $myView = new View('admin');
        $myView->render($user->all());
    }
    // Crée un formulaire avec les données actuelles
    public function update($id)
    {

        $session = new Session;

        $reload = false;
        if (isset($id)) {
            $id = (int) $id['id'];
        } else {
            $reload = true;
        }

        $user  = new model_utilisateur;

        // Appel du Formulaire
        $myView = new View('updatecontact');
        if (!$reload) {
            $contact = $user->find(
                array(
                    'id' => $id,
                )
            );
        } else {
            $contact = $_SESSION['reload'];
        }

        if (is_array($contact) && !empty($contact) && count($contact) == 1) {
            $contact = (array)$contact[0];
        }

        $_SESSION['reload'] = (array)$contact;

        $myView->render();
    }
    // Mise à jour de la base de données 
    public function save()
    {

        $session = new Session;

        if (isset($_POST)) {
            $data = $_POST;
        }

        $validation = new Validation($data);

        $validation->cleaning()
            ->required('nom/Nom de famille', 'prenom/Prénom', 'mail/Adresse Email', 'login/Pseudo')
            ->email('mail/Adresse Email');
        $errors = $validation->getErrors();
        $data = $validation->getData();
        $donnees = $data;

        if (!empty($errors)) {
            // Afficher les erreurs ou effectuer une action en cas d'erreur
            $session->setFlash($errors);
            $_SESSION['reload'] = $data;
            $session->render("admin.update");
        }
        // 1. Ctrl que Login est unique

        // Recherche par login
        $user  = new model_utilisateur;

        // Recherche par login
        $reponse = $user->find(
            array(
                'login' =>  $data['login'],
            )
        );

        $id = $_POST['id'];

        if (!empty($reponse[0]) && $reponse[0]->id != $id) {
            // Rien trouvé -> flash et retour Connexion

            $session->setFlash('Le login existe déjà');
            $_SESSION['reload'] = $donnees;

            $session->render('admin.update');
        }
        // 2. Ctrl que email est unique
        // Recherche par login
        $reponse = $user->find(
            array(
                'mail' =>  $data['mail'],
            )
        );

        $id = $_POST['id'];

        if (!empty($reponse[0]) && $reponse[0]->id != $id) {
            // Rien trouvé -> flash et retour Connexion

            $session->setFlash('Le mail existe déjà');
            $_SESSION['reload'] = $donnees;

            $session->render('admin.update');
        }
        $nom    = $data['nom'];
        $prenom = $data['prenom'];
        $login  = $data['login'];
        $mail   = $data['mail'];
        $niveau   = $data['niveau'];

        // 1.Vérifier si email n'existe pas 

        $user = new model_utilisateur;

        $data = [];

        $data['id']     = $id;
        $data['nom']    = $nom;
        $data['prenom'] = $prenom;
        $data['mail']   = $mail;
        $data['login']  = $login;
        $data['niveau']  = $niveau;

        $user->save($data);
        $session->setFlash('La modification est réussie', 'success');
        unset($_SESSION['reload']);
        $session->render("admin");
    }
    // Appel la page d'inscription
    public function inscription()
    {
        $myView = new View('inscription');
        $titre['titre'] = 'Inscription';
        $myView->render($titre);
    }
    // Contrôle le formulaire d'inscription
    public function inscription_ctrl()
    {

        if (isset($_POST)) {
            $data = $_POST;
        }

        // Récupération des données
        $validation = new Validation($data);

        $validation->cleaning()
            ->required('pseudo/Pseudo', 'nom/Nom', 'prenom/Prénom', 'mail/Email', 'mdp/Mdp', 'mdpbis/Confirmation mdp')
            ->email('mail/Email');
        $errors = $validation->getErrors();
        $data = $validation->getData();
        if ($errors) {
            $msg = $errors;
        }
        $pseudo  = $data['pseudo'];
        $nom    = $data['nom'];
        $prenom = $data['prenom'];
        $mail   = $data['mail'];
        $mdp    = $data['mdp'];
        $mdpbis = $data['mdpbis'];
        $msg = '';
        if ($errors) {
            $msg = $errors;
        } else {
            // 1. Vérifier si mdp est le même que mdpbis,
            if ($mdp !== $mdpbis) {
                $msg = "les mdp ne correspondent pas";
            } else {
                // 2. puis si l'email n'existe pas déjà
                $user = new model_utilisateur;
                $rep_user = $user->find(
                    [
                        'mail' =>  $mail,
                    ]
                );
                // Si il existe :
                if (!empty($rep_user)) {
                    $msg = 'Désolé, l\'email existe déjà dans la base de données.<br />Veuillez choisir un mouvel email';
                }
            }
            if (empty($msg)) {

                // On HASH le mot de passe

                $data = [];
                $data['nom']    = $nom;
                $data['prenom'] = $prenom;
                $data['mdp']    = password_hash($mdp, PASSWORD_BCRYPT);
                $data['niveau']   = 2;
                $data['mail'] = $mail;
                $data['pseudo'] = $pseudo;

                $user->save($data);
            }
            // Les données sont valides, on crée l'email et on l'envoie

            //     include('../phpmailer/envoi-mail.php');
            //     $from = "mail@gvw-tech.be";

            //     $destinataire = $_SESSION['mail'];

            //     $objetmail = "Confirmation d'inscription";


            //     $message = 'Bienvenue sur ifosup.gvw-tech.be, votre inscription a bien été validée.';

            //     $copie_mail = false;
            //     $rep = envoiMail($destinataire, $objetmail, $message, $copie_mail = false, $typeEmail = 'inscription');

            //     if ($rep) {
            //         $msg  = "Votre confirmation a bien été envoyé.<br>";
            //     } else {
            //         $msg  = "Problème d'envoi de mail.<br>";
            //     }

            //     $session->setFlash('Votre inscription est réussie, et vous êtes connecté', 'success');
            //     unset($_SESSION['reload']);
            //     $session->render("accueil");
            // }
           
        }
        echo json_encode($msg);
    }
    // Supprimer compte utilisateur
    public function delete_account()
    {
        $session = new Session;
        if (!Session::$is_auth) {
            $session->setFlash('Opération interdite');
            $session->render('accueil');
        }

        $user  = new model_utilisateur;

        // Recherche par mail
        $reponse = $user->find(
            array(
                'mail' =>  $_SESSION['mail'],
            )
        );
        // Trouvé le User

        $reponse = $reponse[0];

        $id = $reponse->id;

        $user->delete($id);

        // Retour à la page Accueil

        session_destroy();
        $session = new Session();
        $session->setFlash("Vous êtes bien désinscrit(e), nous regrettons de vous voir partir", 'success');
        Session::checkLogin();
        $session->render('accueil');
    }
}
