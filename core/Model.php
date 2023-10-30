<?php
// La "class Model" gère toutes les fonctions SQL pour toutes les tables
class Model{
    public $table = false; 
    public $bdd; 

    // A l'affectation de la classe on ouvre la base de données avec les paramètres et on stocke ça dans l'instance
    public function __construct(){
        $servername = DB_HOST; 
        $dbname     = DB_NAME; 
        $user       = DB_USER; 
        $password   = DB_PASS; 

        try {
            $this->bdd = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $user, $password); // Crée une nouvelle instance de PDO (Php Data Objects) pour établir une connexion à la base de données.
            $this->bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Définit le mode de rapport d'erreurs sur ERRMODE_EXCEPTION, ce qui signifie que les exceptions seront levées en cas d'erreur.
            $this->bdd->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Définit le mode de récupération par défaut des résultats de requête sur FETCH_ASSOC, ce qui signifie que les résultats seront retournés sous la forme associative.
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage(); // Affiche un message d'erreur si la connexion à la base de données échoue.
        }
    }
    // Permet de rechercher tous les enregistrements d'une table qui répondent à des conditions ou pas
    //$req(request),$cond(condition) ,$k(key), $v(value) , $pre(preparation)
    public function find($req = null){
        $sql = 'SELECT * FROM ' . $this->table . ' ';
        
        if (isset($req)){
            $sql .= 'WHERE ';
            if (!is_array($req)) {
                $sql .= $req;
            } else {
                $cond = array();
                foreach ($req as $k => $v) {                
                    if (substr_count($v, '<>') > 0) {
                        $v = str_replace('<>', '', $v);                     
                        $cond[] = "$k<>$v";
                    } else {
                        if (!is_numeric($v)) {
                            $v = $this->bdd->quote($v);
                        };
                        $cond[] = "$k=$v";
                    }
                };
                $sql .= implode(' AND ', $cond);
                if ($this->table == "actualites") {
                    $sql .= " ORDER BY modified_date DESC"; // Remplacez 'date_column_name' par le nom de votre colonne de date
                }
                
            }
        }

        $pre = $this->bdd->prepare($sql);
        $pre->execute();
        return $pre->fetchAll(PDO::FETCH_OBJ);
    }
    
    public function find_in()
    {
        $session = new Session();
        $cart = $_SESSION['panier'];

        $ids = array_keys($cart);
        if (empty($ids)) {
            $arr = array();
        } else {
            $arr = implode(',', $ids);
        }
        $sql = 'SELECT * FROM ' . $this->table . ' ';
        $sql .= 'WHERE id in (' . $arr . ')';

        $pre = $this->bdd->prepare($sql);
        $pre->execute();
        return $pre->fetchAll(PDO::FETCH_OBJ);
    }
    // Récupère tous les enregistrements de la table (classés par ordre décroissant pour les actualités)
    public function all(){
        $sql = 'SELECT * from ' . $this->table . ' ';
       
        // On exécute la requête SQL 
        $resultat = $this->bdd->prepare($sql);
        $resultat->execute();
        $liste = $resultat->fetchAll();

        return  $liste;
    }
    // Supprime un enregistrement de la table de la base de données
    public function delete($id){
        if (isset($_POST['id'])) { 
            $id = $_POST['id'];
        }
        $sql = 'DELETE FROM ' . $this->table . ' WHERE id=:id';

        $query_params = array(':id' => (int) $id);

        try {           
            $stmt = $this->bdd->prepare($sql);          
            $stmt->execute($query_params);
        }
        // Si erreur alors on arrete le script et affiche le message
        catch (PDOException $ex) {
            die("Failed query : " . $ex->getMessage());
        }
    }
    // Cette fonction analyse les datas reçues en paramètres et crée ou met à jour les données dans la table
    public function save($data){
        // var_dump($data);
        // die ("ligne 107");
        //Initialisation des tabeaux et de la variable pk
        $pk = null;
        $fields = array();
        $fields_insert = array();
        $values_insert = array();
        $d      = array();

        // On analyse les data récupérées et on remplit les différents tableaux initialisés
        foreach ($data as $key => $value) {
            if ($key == 'id') {
                $pk = (int) $value;
            } else {
                $fields[] = " $key=:$key";
            }
            $fields_insert[] = "$key";
            $values_insert[] = ":$key";
            if ($key == 'id') {
                $d[":$key"] = (int)$value;
            } else {
                $d[":$key"] = $value;
            }
        }


        // Si "id" est plus grand que "0" on update la table sinon on fait un "insert"
        if (isset($d[":id"]) && ($d[":id"] > 0)) {
            $f = 'Update';
            $sql = "UPDATE " . $this->table . " SET " . implode(',', $fields) . ' WHERE ' . $fields_insert[0] . '=' . $values_insert[0];
        } else {            
            $f = 'Insert';
            $values_insert[':id'] = NULL; 
            $fields_insert[] = "id";  
            
            

            // var_dump($values_insert , $fields_insert);
            // die;      
            $sql = 'INSERT INTO ' . $this->table . ' ( ' . implode(',', $fields_insert) . ' ) VALUES ( ' . implode(',', $values_insert) . ' )';            
        }
        
        try {
           
            $stmt = $this->bdd->prepare($sql);            
            $result = $stmt->execute($d);
        }
        
        catch (PDOException $ex) {
            die("Failed query : " . $ex->getMessage());
        }
        return $result;
    }
    // Fonction qui permet de donner le format en Jour Mois Année 
    static function dateJMA($date){
        $timestamp = strtotime($date);

        // Formatage de la date
        $date_formatee = date('d/m/Y', $timestamp);

        // Affichage de la date
        return $date_formatee;
    }
    
}
