<?php

declare(strict_types=1);

namespace App\Api\v2\Auth;
use App\Object\Classroom;
use App\Api\OneSignal\OneSignal as OneSignal;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


final class Auth{
   private $obj;
    private $json;
    private $signal;
    // $data = $request->getParsedBody();
    function __construct(Classroom $obj, OneSignal $signal)
    {
        $this->obj = $obj;
        $this->signal = $signal;
        $this->json =json_decode(file_get_contents('php://input'));
    }
    private function checkToken($token)
    {
        $checkToken = $token->getHeader('Authorization');
        if (empty($checkToken)) return false;
        $isValid = $this->obj->verifyToken($checkToken[0]);
        return $isValid;
    }
    private function noUserFound($response)
    {
        return $this->obj->respondFailed($response, "Invalid User!!!");
    }
    public function signIn(Request $request, Response $response): Response
    {
      try {
          $check = "SELECT * from users WHERE email = :email && isAdmin != 'Customer'  ";
        $where = array(
            ":email" => $this->obj->sanitizer($this->json->uOe),
        );
        $check = $this->obj->getSingleData($check, $where);
        if ($check) {
            $checkPass = password_verify($this->json->pass, $check[0]->pass);
            if ($checkPass) {
                if ($check[0]->email_verify == 0) {
                    $queryVerify = "UPDATE users set email_verify = 1 where id = :adminId";
                    $bindData = array(
                        ":adminId" => $check[0]->id
                    );
                    $this->obj->ExecuteData($queryVerify, $bindData);
                }
                $tokenData = array(
                    "id" => $check[0]->id,
                    "email" => $check[0]->email,
                    "role" => $check[0]->isAdmin,
                );
                $token = $this->obj->tokenGenerator($tokenData);
                return $this->obj->respondSuccess($response, $token);
            }
            if (!$checkPass) return $this->obj->respondFailed($response, "Wrong Password");
        }
        if (!$check) return $this->obj->respondFailed($response, "Invalid User!!!");
      } catch (\Throwable $th) {
           $this->obj->respondFailed($response, $th);
      }
    }
}