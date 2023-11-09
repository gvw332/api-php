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

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $data['image'] = $_FILES['image']['name'];

        }
     
        $produit = new model_produit;
        $produits = $produit->save($data);

       
        $base = 'C:\Users\Bachibouzouk\Desktop\ecommerce-react\public\images\\';
        $destination = $base . $_FILES['image']['name'];

        if ($produits) {
            $reponse = ['status' => 1, 'message' => "ajout réussi"];
        } else {
            $reponse = ['status' => 0, 'message' => "ajout raté"];
        }
        if (is_writable($base)) {
            // Le répertoire est accessible en écriture
            $destination = $base . $_FILES['image']['name'];

            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                // Le fichier a été déplacé avec succès
                $quid =  'Téléchargement réussi';
            } else {
                // Erreur lors du déplacement du fichier
                $quid = 'Erreur lors du téléchargement du fichier';
            }
        } else {
            // Le répertoire n'est pas accessible en écriture
            $quid =  "Le répertoire de destination n'est pas accessible en écriture";
        }
        echo $quid;
    }
    
    public function Detail()
    {
        $id = $_POST;
        $details  = new model_produit;
        $details = $details->find($id);
        if (empty($details)) {
            echo json_encode('N\'existe pas');
        }else{
            echo json_encode($details);
        }
       

    }


}
