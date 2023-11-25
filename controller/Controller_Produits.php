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
        die;
    }

    public function AjoutProduit()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $data['image'] = $_FILES['image']['name'];

        }
     
        $produit = new model_produit;
        $result = $produit->save($data);

        $base = __DIR__ . '/../public/images/'; // Chemin relatif au dossier images
        $destination = $base . basename($_FILES['image']['name']); // Sécurise le nom du fichier

        $image_sauvegardee = true;

        if (is_writable($base)) {
            // Le répertoire est accessible en écriture
       

            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                // Le fichier a été déplacé avec succès
                $quid =  'Téléchargement réussi';
            } else {
                // Erreur lors du déplacement du fichier
                $quid = 'Erreur lors du téléchargement du fichier';
                $image_sauvegardee = false;
            }
        } else {
            // Le répertoire n'est pas accessible en écriture
            $quid =  "Le répertoire de destination n'est pas accessible en écriture";
            $image_sauvegardee = false;
        }
        if($image_sauvegardee){
            echo json_encode($result);
        }else{
            echo json_encode($quid);
        }
        die;
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
       
        die;
    }

    public function Delete($id)
    {
        $id = $_POST['id'];
        $produit = new model_produit;
        $produits = $produit->delete($id);

       
        die;
    }
}
