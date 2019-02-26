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

$id = $_POST['id'];
// $id=123;
$id = (int)$id;
if (!$id) {
  echo 'error';
  return;
}

try {
  $JSON = file_get_contents('https://eyewire.org/1.0/cell/' . $id);

  if (!$JSON || $JSON === '[]' || $JSON === '{}') {
    return false;
  }
  $JSON = json_decode($JSON);

  $cell_id = $JSON->id;
  $name = $JSON->name;
  $size = $JSON->size;
  $description = $JSON->raw_description;
  $flags = $JSON->is_evil ? ' e' : '';
  $flags .= $JSON->is_hunt ? 'h' : '';
  $flags .= $JSON->is_marathon ? 'm' : '';
  $flags .= $JSON->ai_accelerated ? 'a' : '';
  $flags .= $JSON->is_showcase ? 's' : '';
  $tags = json_encode($JSON->tags);
  $status = !!$JSON->completed;
  $creation_date = $JSON->created;
  $completion_date = $JSON->completed;
  $dataset = $JSON->dataset_id;

  $req = $pdo->prepare('INSERT INTO cells (`cell_id`, `name`, `size`, `description`, `flags`, `tags`, `status`, `creation_date`, `completion_date`, `dataset`) 
    VALUES (:cell_id, :name, :size, :description, :flags, :tags, :status, :creation_date, :completion_date, :dataset)
    ON DUPLICATE KEY UPDATE `cell_id`=VALUES(`cell_id`), `name`=VALUES(`name`), `size`=VALUES(`size`), `description`=VALUES(`description`), `flags`=VALUES(`flags`),
    `tags`=VALUES(`tags`), `status`=VALUES(`status`), `creation_date`=VALUES(`creation_date`), `completion_date`=VALUES(`completion_date`),
    `dataset`=VALUES(`dataset`)
    ');
  $req->bindValue(':cell_id', $cell_id, PDO::PARAM_INT);
  $req->bindValue(':name', $name, PDO::PARAM_STR);
  $req->bindValue(':size', $size, PDO::PARAM_INT);
  $req->bindValue(':description', $description, PDO::PARAM_STR);
  $req->bindValue(':flags', $flags, PDO::PARAM_STR);
  $req->bindValue(':tags', $tags, PDO::PARAM_STR);
  $req->bindValue(':status', $status, PDO::PARAM_STR);
  $req->bindValue(':creation_date', $creation_date, PDO::PARAM_STR);
  $req->bindValue(':completion_date', $completion_date, PDO::PARAM_STR);
  $req->bindValue(':dataset', $dataset, PDO::PARAM_INT);
  if ($req->execute()) {
    $prefix = 'cells_from_db_';
    $suffix = '.js';

    // source: https://stackoverflow.com/a/15469249
    array_map('unlink', glob($prefix . '*' . $suffix));
  
    $result = $pdo->query("SELECT `cell_id`, `name`, `size`, `description`, CASE WHEN `status` = 1 THEN 'Completed' ELSE NULL END, `creation_date`, `completion_date`, `dataset` FROM cells");
  
    if ($result->rowCount()) {
      $result = json_encode(utf8ize($result->fetchAll(PDO::FETCH_NUM)));
      $fileName = $prefix . time() . $suffix;
      file_put_contents($fileName, 'document.Kcells=' . $result);
    }
    echo 'ok';
  }
  else {
    echo 'error';
  }


}
catch (Exception $e) {
  echo 'error';
  return;
}

// source: https://stackoverflow.com/a/52641198
function utf8ize($mixed) {
  if (is_array($mixed)) {
    foreach ($mixed as $key => $value) {
      $mixed[$key] = utf8ize($value);
    }
  } elseif (is_string($mixed)) {
    return mb_convert_encoding($mixed, 'UTF-8', 'UTF-8');
  }
  return $mixed;
}
