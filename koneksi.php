<?php
$host = "sql211.infinityfree.com"; 
$user = "if0_43152187";          
$pass = "7DMPdjXQWBpEzIL";       
$db   = "if0_43152187_mydashboard"; 

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>