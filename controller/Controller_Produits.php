<?php
// Fonctions servant à appeler les pages qui n'ont pas de données provenant de la base de données 
class Controller_Produits extends Controller
{
    // Affichage de la page d'accueil

    public function Produits()
    {
   
        $produit = new model_produit;
        $produits = $produit->all();

        echo json_encode($produits);
    }

    public function AjoutProduit()
    {
        $data = $_POST;
        // $data = json_decode('php://input');
        $produit = new model_produit;
        $produits = $produit->save($data);
        if ($produits){
            $reponse = ['status' => 1, 'message' => "ajout réussi"];
        }else{
            $reponse = ['status' => 0, 'message' => "ajout raté"];
        }
    }

}
