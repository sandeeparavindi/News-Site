<?php
$servername = "localhost";
$username = "root"; 
$password = "Ijse@1234";      
$dbname = "news_admin";

function getDatabaseConnection() {
    global $servername, $username, $password, $dbname;
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}
?>