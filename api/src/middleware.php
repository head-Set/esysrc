<?php
include __DIR__ . '/config/Dbconfig.php';
include __DIR__ . '/Object/Classroom.php';
include __DIR__ . '/config/core.php';

// $connection = new Dbconfig('127.0.0.1','test','root','');

$headersJWT = getallheaders()['Authorization'];
$data = json_decode(file_get_contents('php://input'));
// $con = $connection->getConnection();
$obj = new Classroom('', $iss, $aud, $iat, $nbf, $key);
