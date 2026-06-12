<?php
$c = new PDO('mysql:host=127.0.0.1:3307;dbname=caravana_bot', 'root', '');
$s = $c->query('SHOW COLUMNS FROM user');
while($r = $s->fetch()) echo $r['Field'] . " " . $r['Type'] . " " . $r['Extra'] . "\n";
