<?php
$servername = "127.0.0.1";
$username = "sa";
$password = "sa";
$dbname = "opnoteess";
// Create connection
$conn3 = new mysqli($servername, $username, $password,$dbname);

// Check connection
if ($conn3->connect_error) {
    die("Connection failed: " . $conn3->connect_error);
}
mysqli_query($conn3,"SET character_set_results=utf8");
mysqli_query($conn3,"SET character_set_client=utf8");
mysqli_query($conn3,"SET character_set_connection=utf8");
?>