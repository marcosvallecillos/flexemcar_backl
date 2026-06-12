<?php
$c = new PDO('mysql:host=127.0.0.1:3307;dbname=flexemcar', 'root', '');
$s = $c->query('SHOW COLUMNS FROM vehicles');
while($r = $s->fetch()) echo $r['Field'] . " " . $r['Type'] . "\n";
