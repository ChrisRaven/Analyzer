<?php
require '../credentials/pass.php';

$pdo = new PDO(
  "mysql:host={$host};dbname={$dbname};charset=utf8", $user, $pass,
  [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

$domain = $_SERVER['HTTP_ORIGIN'];

if (!in_array($domain, ['https://eyewire.org', 'https://beta.eyewire.org', 'https://chris.eyewire.org', 'https://dev1.eyewire.org'])) {
  exit('incorrect domain');
}

session_start();

header('Access-Control-Allow-Origin: ' . $domain);
header('Access-Control-Allow-Credentials: true'); // source: https://stackoverflow.com/a/47993517


$result = $pdo->query("SELECT `cell_id`, `name`, `size`, `description`, CASE WHEN `status` = 1 THEN 'Completed' ELSE NULL END, `creation_date`, `completion_date`, `dataset` FROM cells");

if ($result->rowCount()) {
  echo json_encode($result->fetchAll(PDO::FETCH_NUM));
}

