<?php

declare(strict_types=1);

namespace App\Api\PasswordReset;

use App\Object\Classroom;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PasswordReset
{
  private $obj;
  private $json;
  // $data = $request->getParsedBody();
  function __construct(Classroom $obj)
  {
    $this->obj = $obj;
    $this->json = json_decode(file_get_contents('php://input'));
  }

  public function resetpassword(Request $request, Response $response, $params): Response
  {
    $type = $params['type'];
    $email = $this->obj->sanitizer($this->json->email);
    $newPass = $this->obj->sanitizer($this->json->pass);
    $hash = password_hash($newPass, PASSWORD_BCRYPT);
    if ($type == 'customer' || $type == 'Customer') {
      $checkUser = "SELECT email, id from users where email = :email && isAdmin = 'Customer'";
    }
    if ($type == 'provider' || $type == 'Provider') {
      $checkUser = "SELECT email, id from providers where email = :email";
    }
    $bindData = array(
      ":email" => $email
    );
    $result = $this->obj->getSingleData($checkUser, $bindData);
    if (empty($result)) return $this->obj->respondFailed($response, "Email Not Registerd to our System");

    $insertQuery = "INSERT INTO passwordreset values(null,:id,:type,:password)";
    $insertData = array(
      ":id" => $result->id,
      ":type" => $type,
      ":password" => $hash
    );
    $insertNewPassword = $this->obj->CreateData($insertQuery, $insertData);
    if (empty($insertNewPassword)) return $this->obj->respondFailed($response, "SQL ERROR");;
    if (!empty($insertNewPassword)) {
      mail($email, 'ESY ACCOUNT VERIFICATION', "Please Click the link to verify you New Password:  https://esy.fusiontechph.com/password/verify/" . (int)$insertNewPassword);
      return $this->obj->respondSuccess($response, "Please Verify your Email");
    }
  }
  public function verifypassword(Request $request, Response $response, $params): Response
  {
    $passwordResetId = (int)$params['resetId'];

    $checkInfo = $this->obj->getData("SELECT * from passwordreset where id = $passwordResetId");
    if (!empty($checkInfo)) {
      $userId = $checkInfo[0]->userid;
      $userType = $checkInfo[0]->type;
      $newPass = $checkInfo[0]->password;

      if ($userType == 'customer' || $userType == 'Customer') {
        $checkUser = "UPDATE users set password = :newPass where id = :userId";
      }
      if ($userType == 'provider' || $userType == 'Provider') {
        $checkUser = "UPDATE providers set password = :newPass where id = :userId";
      }
      $bindData = array(
        ":newPass" => $newPass,
        ":userId" => $userId
      );
      $this->obj->ExecuteData($checkUser, $bindData);

      $response->getBody()->write("Password Reset Success Please Log In");
      $response->withStatus(201);
      return $response;
    }
    $response->getBody()->write("Link Expired");
    $response->withStatus(300);
    return $response;
  }
};
