<?php

namespace App\Object;

include_once __DIR__ . '../../libs/php-jwt-master/src/BeforeValidException.php';
include_once __DIR__ . '../../libs/php-jwt-master/src/ExpiredException.php';
include_once __DIR__ . '../../libs/php-jwt-master/src/SignatureInvalidException.php';
include_once __DIR__ . '../../libs/php-jwt-master/src/JWT.php';


use \Firebase\JWT\JWT;
use PDO;

class Classroom{

    private $conn = null;
    private $iss = 'http://eesy.fusiontechph.com';
    private $aud = 'https://esy.fusiontechph.com';
    private $iat = 1356999524;
    private $nbf = 1357000000;
    private $key = 's3cr3t0n6m4lu93t';
    public function __construct(PDO $conn){
        $this->conn = $conn;
    }
    public function tokenGenerator($data){
        $token = array(
            "iss" => $this->iss,
            "aud" => $this->aud,
            "iat" => $this->iat,
            "nbf" => $this->nbf,
            "data" => array(
                'id'=>$data['id'],
                'email'=>$data['email'],
                'role'=>$data['role']
            ),
        );
        http_response_code(200);
        $jwt = JWT::encode($token, $this->key);
        return (array(
            "jwt" => $jwt
        ));
    }
    public function verifyToken($request){
        // try {
        //     $token = $request->getHeader('Authorization');
        //     // getHeaderLine
        //     if ($token) {
        //         $decode = JWT::decode($token[0], $this->key, array('HS256'));
        //         return $decode->data;
        //     } 
        //     http_response_code(401);
        //     return (array(
        //         "code" => 400,
        //         "message" => "Access denied.",
        //         "error" => $e->getMessage()
        //     ));
        // } catch (Exception $e) {
        //     // http_response_code(401);
        //     return (array(
        //         "code" => 400,
        //         "message" => "Access denied.",
        //         "error" => $e->getMessage()
        //     ));
        // }
        $decode = JWT::decode($request, $this->key, array('HS256'));
        if($decode){
            try{
                return $decode->data;
            }catch(Exception $e){
             return (array(
                    "code" => 400,
                    "message" => "Access denied.",
                    "error" => $e->getMessage()
                ));   
            }
        }
    }
    public function getData($query){
        try {
            $sql = $this->conn->prepare($query);
            $sql->execute();
            return $sql->fetchAll();
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
    public function getSingleData($query, $bind){
        try {
            $sql = $this->conn->prepare($query);
            foreach ($bind as $key => $value) {
                $sql->bindValue($key, $value);
            }
            $sql->execute();
            return $sql->fetchAll();
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
    public function CreateData($query, $bind){
        try {
            $sql = $this->conn->prepare($query);
            foreach ($bind as $key => $value) {
                $sql->bindValue($key, $value);
            }
            $result = $sql->execute();
            if ($result) {
                $resp = (int) $this->conn->lastInsertId();
            }
            if (!$result) {
                $resp = 'Successfully Failed';
            }
            return json_encode($resp);
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
    public function ExecuteData($query, $bind){
        try {
            $sql = $this->conn->prepare($query);
            foreach ($bind as $key => $value) {
                $sql->bindValue($key, $value);
            }
            $result = $sql->execute();
            if ($result) {
                $resp =(int) $sql->rowCount();
            }
            if (!$result) {
                $resp = false;
            }

            return json_encode($resp);
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }
    public function sanitizer($data){
        return htmlspecialchars(strip_tags($data));
    }
    public function getToDay(){
        $dateObj = getdate();
        return $dateObj['year'].'-'.$dateObj['mon'].'-'.$dateObj['mday'];
    }
    public function getToDayAndTime(){
        $dateObj = getdate();
        $time  =$dateObj['hours']. ":" .$dateObj['minutes']; 
        $date = $dateObj['year'].'-'.$dateObj['mon'].'-'.$dateObj['mday'] ;
        return $date.' '. $time ;
    }
    
    public function respondFailed($response, $data){
        $response->getBody()->write(json_encode(array(
                "code"=>400,
                "message"=>$data,
            )));
        return $response;
    }
    public function respondSuccess($response, $data){
        $response->getBody()->write(json_encode(array(
            "code"=>200,
            "message"=>'Success',
            "data"=>$data
        )));
        return $response;
    }
}
