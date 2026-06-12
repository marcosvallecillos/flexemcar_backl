<?php
ob_start();
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=caravana_bot', 'root', '');
$stmt = $pdo->query('SHOW COLUMNS FROM vehicle_models');
echo "caravana_bot.vehicle_models:\n";
foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo $row['Field'] . ", ";
}
echo "\n\n";

$pdo2 = new PDO('mysql:host=127.0.0.1;port=3307;dbname=flexemcar', 'root', '');
$stmt2 = $pdo2->query('SHOW COLUMNS FROM vehicles');
echo "flexemcar.vehicles:\n";
foreach($stmt2->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo $row['Field'] . ", ";
}
echo "\n";
file_put_contents('out2.txt', ob_get_clean());
