<?php
header('Content-Type: application/json; charset=utf-8');

// Later te vervangen door een echte databasequery, bijv.:
// $pdo = new PDO('mysql:host=localhost;dbname=theater', 'root', '');
// $stmt = $pdo->query('SELECT titel, datum, locatie FROM voorstellingen ORDER BY datum ASC');
// echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

$voorstellingen = [
  ['titel' => 'De Storm',         'datum' => '2026-09-12', 'locatie' => 'Grote Zaal'],
  ['titel' => 'Zwanenmeer',       'datum' => '2026-09-20', 'locatie' => 'Stadsschouwburg Amsterdam'],
  ['titel' => 'Hamlet',           'datum' => '2026-10-03', 'locatie' => 'Kleine Zaal'],
  ['titel' => 'De Verwachting',   'datum' => '2026-10-18', 'locatie' => 'Zuiderpershuis Antwerpen'],
  ['titel' => 'Licht & Schaduw',  'datum' => '2026-11-01', 'locatie' => 'Grote Zaal'],
  ['titel' => 'Nachtmerrie',      'datum' => '2026-11-14', 'locatie' => 'Theater Rotterdam'],
  ['titel' => 'Het Afscheid',     'datum' => '2026-12-05', 'locatie' => 'Kleine Zaal'],
];

echo json_encode($voorstellingen, JSON_UNESCAPED_UNICODE);
