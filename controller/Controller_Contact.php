<?php
// Gère toutes les fonctions de contacts
class Controller_Contact extends Controller{
    // Affiche la page de contact 
    public function contact(){
        $myView = new View('contact');
        $titre['titre'] = 'Contact';
        $myView->render($titre);
    }
    // Vérifie les données du formulaire 
    public function contact_ctrl($data){
        $session = new Session;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if (isset($_SESSION['contact.ctrl'])) {
                $session->render("contact");
                exit;
            }
        }
        if (empty($_POST)) {
            $session->setFlash('Vous ne pouvez pas revenir en arrère.');
            $session->render("contact");
        }

        $validation = new Validation($data);

        $validation->cleaning()
            ->required('nom', 'prenom', 'email', 'message')
            ->email('email');

        $errors = $validation->getErrors();
        $data   = $validation->getData();

        if (isset($_SESSION['reload'])) unset($_SESSION['reload']);

        if (!empty($errors)) {

            $session->setFlash($errors);
            $_SESSION['reload'] = $data;
            $session->render("contact");
        } else {
            // Les données sont valides, on crée l'email et on l'envoie
            include('../phpmailer/envoi-mail.php');
            $from = "mail@gvw-tech.be";

            $destinataire = $data['email'];

            $objetmail = "Demande de renseignement";


            $message = $data['message'];

            $copie_mail = true;
            $rep = envoiMail($destinataire, $objetmail, $message, $copie_mail = true, $typeEmail = 'contact');

            if ($rep) {
                $msg  = "Votre message a bien été envoyé.<br>";
            } else {
                $msg  = "Problème d'envoi veuillez réessayer plus tard.<br>";
            }

            $session->setFlash($msg, 'success');
            $session->render("accueil");
        }
    }
}