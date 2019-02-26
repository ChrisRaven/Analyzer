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

$author = $_POST['author'];
$uuid = $_POST['uuid'];

$req = $pdo->prepare('DELETE FROM workspaces WHERE author = :author AND uuid = :uuid');
$req->bindValue(':author', $author, PDO::PARAM_STR);
$req->bindValue(':uuid', $uuid, PDO::PARAM_STR);
$req->execute();

if ($req->rowCount()) {
  echo 'ok';
}
