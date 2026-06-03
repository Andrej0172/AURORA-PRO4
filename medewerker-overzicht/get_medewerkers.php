<?php
header('Content-Type: application/json; charset=utf-8');

// Later te vervangen door een echte databasequery, bijv.:
// $pdo = new PDO('mysql:host=localhost;dbname=theater', 'root', '');
// $stmt = $pdo->query('SELECT naam, functie, afdeling FROM medewerkers');
// echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

$medewerkers = [
  ['naam' => 'Sophie de Vries',   'functie' => 'Regisseur',       'afdeling' => 'Artistiek'],
  ['naam' => 'Lars Bakker',       'functie' => 'Acteur',           'afdeling' => 'Artistiek'],
  ['naam' => 'Noor van den Berg', 'functie' => 'Lichtontwerper',   'afdeling' => 'Techniek'],
  ['naam' => 'Daan Janssen',      'functie' => 'Geluidsontwerper', 'afdeling' => 'Techniek'],
  ['naam' => 'Emma Smit',         'functie' => 'Kostuumontwerper', 'afdeling' => 'Kostuums'],
  ['naam' => 'Tim Visser',        'functie' => 'Productieleider',  'afdeling' => 'Productie'],
  ['naam' => 'Lisa Meijer',       'functie' => 'Marketingmanager', 'afdeling' => 'Marketing'],
];

echo json_encode($medewerkers, JSON_UNESCAPED_UNICODE);
