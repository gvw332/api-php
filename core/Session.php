<?php
// Gère toutes les variables de sessions
class Session
{
    static $is_auth = false;
    static $is_admin = false;
    static $login = '';

    // A chaque fois qu'on fait une new session le constructeur va regarder si il existe une session en cours
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->checkLogin();
    }
    // Contrôle l'identification pour voir si l'utilisateur est authentifié ou pas et s'il est admin ou pas
    static function checkLogin()
    {
        if (isset($_SESSION['niveau'])) {
            self::$login = $_SESSION['pseudo'];
            if ($_SESSION['niveau'] == 1) {
                self::$is_auth = true;
                self::$is_admin = true;
            } else if ($_SESSION['niveau'] == 2) {
                self::$is_auth = true;
                self::$is_admin = false;
            }
        } else {
            self::$login = '';
            self::$is_auth = false;
            self::$is_admin = false;
        }
    }
    // Crée des messages ; $Color {danger=rouge, success=vert}
    public function setFlash($message, $color = "danger")
    {
        $_SESSION['flash'] = array(
            "message" => $message,
            "type" => $color,
        );
    }
    // Crée l'affichage du message
    public function flash()
    {
        if (isset($_SESSION['flash'])) {
?>
            <div id="alert" class="text-center alert alert-<?php echo $_SESSION['flash']['type']; ?>">
                <?php echo $_SESSION['flash']['message']; ?>
            </div>
<?php
            unset($_SESSION['flash']);
        }
    }
    // Renvoie une page avec des valeurs ou pas 
    public function render($route, $values = null)
    {
        if (is_null($values)) {
            header("Location: " . HOST . $route);
            exit;
        } else {
            header("Location: " . HOST . $route . '/' . $values);
            exit;
        }
    }

    // Compte les éléments du panier
    public function count($panier = "panier")
    {
        if (isset($_SESSION[$panier])) {
            return array_sum($_SESSION[$panier]);
        }

        return 0;
    }

    // Calcule le total du panier
    public function total($panier = "panier")
    {
        $products = new model_produit();
        $total = 0;
        if (isset($_SESSION['panier'])) {
            $ids = array_keys($_SESSION[$panier]);
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
                foreach ($lines as $line) {
                    $total += $line->price * $cart[$line->id];
                }
            } else {
                $arr = array();
            }
        }
        return $total;
    }
}
