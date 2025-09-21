<?php

$clave_plain = "francomokwa";
$clave_hash = password_hash($clave_plain, PASSWORD_DEFAULT);
echo $clave_hash;

?>