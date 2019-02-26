<?php
require '../credentials/pass.php';

$pdo = new PDO(
  "mysql:host={$host};dbname={$dbname};charset=utf8", $user, $pass,
  [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
);

$result = $pdo->query("SELECT `cell_id`, `name`, `size`, `description`, CASE WHEN `status` = 1 THEN 'Completed' ELSE NULL END, `creation_date`, `completion_date`, `dataset` FROM cells");

if ($result->rowCount()) {  
  $prefix = 'cells_from_db_';
  $suffix = '.js';

  // source: https://stackoverflow.com/a/15469249
  array_map('unlink', glob($prefix . '*' . $suffix));

  $result = json_encode(utf8ize($result->fetchAll(PDO::FETCH_NUM)));

  $fileName = $prefix . time() . $suffix;
  file_put_contents($fileName, 'document.Kcells=' . $result);
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
