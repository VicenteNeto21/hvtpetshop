<?php
$db = new PDO('mysql:host=localhost;dbname=hvt_petshop', 'root', ''); 
echo "Tutores:\n";
$stmt = $db->query('DESCRIBE tutores'); 
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
echo "\nPets:\n";
$stmt = $db->query('DESCRIBE pets'); 
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
