<?php


use Stripe\Stripe;



class Controller_Stripe extends Controller
{
    public function paiement()
    {
        require_once '../vendor/autoload.php';

        $stripe = new \Stripe\StripeClient(SK_STRIPE);

        function calculateOrderAmount(array $items): int
        {
            $totalAmount = 0;

            foreach ($items as $item) {
                $totalAmount += $item->itemTotal * 100;
            }
            return $totalAmount;
        }

        header('Content-Type: application/json');

        try {
            // retrieve JSON from POST body
            $jsonStr = file_get_contents('php://input');

            $jsonObj = json_decode($jsonStr);
            //$panier = $jsonObj->panier;

            // Create a PaymentIntent with amount and currency
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => calculateOrderAmount($jsonObj->items),
                'currency' => 'eur',
                // In the latest version of the API, specifying the automatic_payment_methods parameter is optional because Stripe enables its functionality by default.
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            $output = [
                'clientSecret' => $paymentIntent->client_secret,
                'message' => 'Bravo',
            ];
            http_response_code(200);
            echo json_encode($output);
        } catch (Error $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

}
