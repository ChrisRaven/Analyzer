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

$cells = $_POST['cells'];
$cells = json_decode($cells);

switch ($cells->rights) {
  case 'private': $rights = 0; break;
  case 'readonly': $rights = 1; break;
  case 'fullaccess': $rights = 2; break;
  default: $rights = 0;
}

$req = $pdo->prepare('REPLACE INTO workspaces (`uuid`, `name`, `description`, `cells`, `author`, `rights`, `creation_date`, `world_position`, `active`, `last_selected_cube`)
VALUES (:uuid, :name, :description, :cells, :author, :rights, NOW(), :world_position, :active, :last_selected_cube)');
$req->bindValue(':uuid', $cells->uuid, PDO::PARAM_STR);
$req->bindValue(':name', $cells->name, PDO::PARAM_STR);
$req->bindValue(':description', $cells->description, PDO::PARAM_STR);
$req->bindValue(':cells', json_encode($cells->cells), PDO::PARAM_STR);
$req->bindValue(':author', $cells->author, PDO::PARAM_STR);
$req->bindValue(':rights', $rights, PDO::PARAM_INT);
$req->bindValue(':world_position', json_encode($cells->worldPosition), PDO::PARAM_STR);
$req->bindValue(':active', $cells->active, PDO::PARAM_INT);
$req->bindValue(':last_selected_cube', $cells->lastSelectedCube ?: NULL, PDO::PARAM_INT);

$req->execute();
