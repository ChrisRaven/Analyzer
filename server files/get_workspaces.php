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

$req = $pdo->prepare('SELECT `name`, `description`, `cells`, `creation_date`, `author`, `tags`, `rights`, "server" as "source", `uuid`, `world_position`, `active`, `last_selected_cube` FROM workspaces WHERE author = :author OR rights = 1 OR rights = 2');
$req->bindValue(':author', $author, PDO::PARAM_STR);
$req->execute();

if ($req->rowCount()) {
  echo json_encode($req->fetchAll(PDO::FETCH_NUM));
}
