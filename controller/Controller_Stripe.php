<?php


use Stripe\Stripe;



class Controller_Stripe extends Controller
{
    public function paiement()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        var_dump($_POST);
        die('Ligne 15');
        // Vérifier si 'cardData' existe dans la requête POST
        if (isset($_POST['cardData'])) {
            $token = $_POST['cardData']['token'];

            require_once('vendor/autoload.php');
            Stripe::setApiKey('sk_test_51O2YZFIzz2DktgswhZJVZc6EKXH0sDIEJhHscxVh6yB3Uchev48gsh3k2qfRIjsBPfvvEAxsgDaY2JtVM8Q0k23R00L7Php09P');

            $charge = \Stripe\Charge::create([
                'amount' => 2000, // Montant en cents
                'currency' => 'eur',
                'source' => $token,
                'description' => 'Example charge',
            ]);

            echo json_encode($charge);
        } else {
            // La clé 'cardData' est absente dans la requête POST
            echo json_encode(['error' => 'Données de carte absentes']);
        }
    }
    public function success()
    {
        $myView = new View('success');
        $titre['titre'] = 'Success';
        $myView->render($titre);
    }
}
