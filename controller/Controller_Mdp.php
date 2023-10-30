<?php
// Gère toutes les fonctions qui concernent les utilisateurs et les mots de passes oubliés
class Controller_Mdp extends Controller{
    // Affiche la page de demande de changement de mot de passe
    public function mdp_demande(){
        $myView = new View('mdp-demande');
        $myView->render();
    }
    // Contrôle le formulaire de la page de demande de changement de mot de passe
    public function mdp_demande_ctrl(){
        $session = new Session;
        $validation = new Validation($_POST);

        $validation->cleaning()
            ->required('login/Email')
            ->email('login/Email');

        $errors = $validation->getErrors();
        $data = $validation->getData();

        $email_recherche = $data['login'];

        if (isset($_SESSION['reload'])) unset($_SESSION['reload']);
        // Vérifie si il y a des erreurs
        if (!empty($errors)) {
            // Afficher les erreurs ou effectuer une action en cas d'erreur
            $session->setFlash($errors);        // 1. Je crée les messages
            $_SESSION['reload'] = $data;        // 2. Je mémorise les data
            $session->render("mdp-demande");
        }
        // On regarde si l'email existe dans la table User
        $user = new model_utilisateur;
        $rep_user = $user->find(
            array(
                'mail' => $email_recherche,
            )
        );
        // Si Rien trouvé -> flash et retour mdp-oublie
        if (empty($rep_user)) {
            $session->setFlash('Cet email n\'existe pas');
            $_SESSION['reload'] = $data;
            $session->render('mdp-demande');
        }

        // Création d'un mot de passe temporaire 'Token'
        $token = bin2hex(openssl_random_pseudo_bytes(24));
        $user_id = $rep_user[0]->id;
        // On regarde si l'email existe dans la table Recover

        $recover = new model_recover;
        $rep_recover = $recover->find(
            array(
                'mail' =>  $email_recherche,
            )
        );
        $id = 0;
        if (!empty($rep_recover)) {
            $rep_recover = $rep_recover[0];
            $id = $rep_recover->id;
        }

        // si L'email est trouvé, on fera un UPDATE, sinon un INSERT

        $data = [];
        $data['id'] = 0;
        if ($id > 0) {
            $data['id'] = $id;
        }
        $data['mail'] = $email_recherche;
        $data['token'] = $token;
        $data['user_id'] = $user_id;
      
        $recover->save($data);

        // On va envoyer un email au correspondant avec l'email et le Token dans un lien cliquable
        $link = 'nouveau-mdp?u=' . $email_recherche . '&token=' . base64_encode($token);

        include('../phpmailer/envoi-mail-mdp.php');
        $from = "mail@gvw-tech.be";
        $destinataire = $email_recherche;
        $objetmail = "Lien pour obtenir un nouveau mot de passe";
        $headers = "From:" . $from;     
        $message = HOST . $link;       

        $rep = envoiMailMdp($destinataire, $objetmail, $message);

        if ($rep) {
            $msg = "Un mail vient de vous être envoyé<br>";
            $msg .= "Veuillez cliquer sur le lien de ce mail pour compléter la procédure<br>";
            $msg .= "Merci";
            $session->setFlash($msg, 'success');
        } else {
            $msg  = "Problème d'envoi veuillez réessayer plus tard.<br>";
            $session->setFlash($msg);
        }
        $session->render('accueil');
        exit;
    }
    // Vérifie les données reçues du lien cliquable de l'email
    public function changer_mdp(){
        $session = new Session;

        if (isset($_GET['u']) && isset($_GET['token'])) {
            $email = $_GET['u'];
            $token = $_GET['token'];
            $hiddentoken = base64_decode($token);
            $bloc1 = [];
            $bloc1['email'] = $email;
            $bloc1['token'] = $hiddentoken;
        } else {
            if (isset($_SESSION['recup'])) {
                $email = $_SESSION['recup']['email'];
                $hiddentoken = $_SESSION['recup']['token'];
                if (isset($_SESSION['recup']['user_id'])) {
                    $user_id = $_SESSION['recup']['user_id'];
                }
                if (isset($_SESSION['recup']['id'])) {
                    $user_id = $_SESSION['recup']['id'];
                }
            }
        }
        // On va vérifier que l'email avec le token sont bien dans la table recover
        $recover = new model_recover;

        $rep_recover = $recover->find(
            [
                'mail' =>  $email,
                'token' => $token,
            ]
        );
        // Si pas trouvé:
        if (empty($rep_recover)) {
            // Afficher les erreurs ou effectuer une action en cas d'erreur
            $session->setFlash('Désolé, il n\'y a rien qui correspond à cette demande');
            $session->render("accueil");
        }
        $_SESSION['recup'] = $this->objToArray($rep_recover[0]);
        // Si trouvé:
        $myView = new View('nouveau-mdp');
        $myView->render();
    }
    // Appel de la page pour créer un nouveau mot de passe
    public function nouveau_mdp(){
        $myView = new View('nouveau-mdp');
        $myView->render();
    }
    // Contrôle du mot de passe du formulaire nouveau_mdp
    public function nouveau_mdp_ctrl(){
        $session = new Session;

        if (isset($_SESSION['reload'])) {
            unset($_SESSION['reload']);
        }

        if (isset($_POST)) {
            $data_post = $_POST;
        }

        $hiddenToken = $data_post['token'];
        $hiddenEmail = $data_post['email'];

        $validation = new Validation($data_post);
        $validation->cleaning()
            ->required('mdp/Mot de passe', 'mdpbis/Confirmer mot de passe'); // obligé de mettre mdp/ et mdpbis/ sinon erreur
        $errors = $validation->getErrors();
        $data = $validation->getData();

        if (!empty($errors)) {
            $session->setFlash($errors);        
            $_SESSION['reload'] = $data;        
            $session->render("nouveau-mdp");
        }

        $mdp = $data['mdp'];
        $mdpbis = $data['mdpbis'];

        if ($mdp !== $mdpbis) {
            $session->setFlash('Les mots de passe ne concordent pas.');
            $_SESSION['reload'] = $data;
            $session->render("nouveau-mdp");
        }

        $recover = new model_recover;
        $rep_recover = $recover->find(
            array(
                'mail' => $hiddenEmail,
            )
        );

        if (empty($rep_recover)) {
            // Afficher les erreurs ou effectuer une action en cas d'erreur
            $session->setFlash('Désolé, il n\'y a rien qui correspond à cette demande');
            $session->render("accueil");
        }

        $data_recover = (array)$rep_recover[0];

        $id      = $data_recover['id'];
        $user_id = $data_recover['user_id'];

        $token   = $data_recover['token'];
        $email   = $data_recover['mail'];

        if ($token !== base64_decode($hiddenToken)) {
            $session->setFlash('La sécurité est compromise.');
            if (isset($_SESSION['reload'])) {
                unset($_SESSION['reload']);
            }
            $session->render("accueil");
        }

        $mdp = password_hash($mdp, PASSWORD_BCRYPT);

        $data = [];
        $data['id'] = $user_id;
        $data['mdp'] = $mdp;

        // Mise à jour du nouveau mdp dans User
        $user  = new model_utilisateur;
        $user->save($data);
        // Suppression du token provisoir dans Recover
        $recover = new model_recover;
        $recover->delete($id);

        $session->setFlash('Votre mot de passe a bien été mis à jour.', 'success');

        $session->render("accueil");
        exit;
    }
}