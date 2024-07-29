<?php
// config.php
define('DB_SERVER', 'dbserver');
define('DB_USERNAME', 'wallet_user');
define('DB_PASSWORD', '8dhaZ#LrV9QAE6');
define('DB_NAME', 'wallet_dev');

/* Connessione al database */
$link = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verifica la connessione
if($link === false){
    die("ERROR: Could not connect. " . $link->connect_error);
}
?>
