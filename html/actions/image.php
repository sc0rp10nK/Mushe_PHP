<?php
require_once '../api/function.php';

$db = getDb();

$sql = 'SELECT * FROM USER_PROFILE_IMAGES WHERE image_userid = :image_userid LIMIT 1';
$stmt = $db->prepare($sql);
$stmt->bindValue(':image_userid', $_GET['id'], PDO::PARAM_STR);
$stmt->execute();
$image = $stmt->fetch();
var_dump($image);
header('Content-type: ' . $image['image_type']);
echo $image['image_content'];
unset($db);
exit();
?>