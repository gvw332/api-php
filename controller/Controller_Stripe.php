<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


use Stripe\Stripe;



class Controller_Stripe extends Controller
{
    public function paiement()
    {
        $session = new Session();

        $cart = $_SESSION['panier'];

        if (!empty($cart)) {

            $ids = array_keys($cart);
            if (empty($ids)) {
                $arr = array();
            } else {
                $arr = implode(',', $ids);
            }
            $products  = new model_produit;
            $lines = $products->find_in();
        }

        $line_items = [];
        foreach ($lines as $item) {
            $line_item = [
                'quantity' => $_SESSION['panier'][$item->id],
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int)$item->price * 100, // Convertir le prix en centimes
                    'product_data' => [
                        'name' => $item->title
                    ]
                ],
            ];
            array_push($line_items, $line_item);
        }

        Stripe::setApiKey(SK_STRIPE);

        Stripe::setApiVersion('2023-10-16');


        // $line_items = [
        //     [
        //         'quantity' => 1,
        //         'price_data' => [
        //             'currency' => 'eur',
        //             'unit_amount' => 2500,
        //             'product_data' => [
        //                 'name' => 'Ligne de vente'
        //             ]
        //         ],
        //     ],
        //     [
        //         'quantity' => 3,
        //         'price_data' => [
        //             'currency' => 'eur',
        //             'unit_amount' => 800,
        //             'product_data' => [
        //                 'name' => 'Ligne de vente 2'
        //             ]
        //         ],
        //     ]
        // ];

        $checkout_session = \Stripe\Checkout\Session::create([
            'line_items' => $line_items,
            'mode' => 'payment',
            // 'automatic_tax' => ['enabled' => true],
            'success_url' => HOST . 'success',
            'cancel_url' => HOST . 'shopping',

        ]);
        $_SESSION['checkout_session'] = $line_items;
        header("HTTP/1.1 303 See Other");
        header("Location: " . $checkout_session->url);
    }
    public function success()
    {
        $myView = new View('success');
        $titre['titre'] = 'Success';
        $myView->render($titre);
    }

}
