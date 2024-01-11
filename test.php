<?php 
$fuck = "duck";
$you = [0,1,2,3,4,5];

array_splice($you, 8, 0, $fuck);

echo "<pre>";
var_dump($you);
echo "</pre>";

?>