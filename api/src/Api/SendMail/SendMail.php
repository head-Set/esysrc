<?php

declare(strict_types=1);

namespace App\Api\SendMail;

use App\Object\Classroom;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class SendMail{
    private $obj;
  private $json;
  // $data = $request->getParsedBody();
  function __construct(Classroom $obj)
  {
    $this->obj = $obj;
    $this->json = json_decode(file_get_contents('php://input'));
  }
    public function sendMail(Request $request, Response $response, $params): Response{
        $headers="Content-type: text/html; charset=iso-8859-1rn";
        // $message=file_get_contents(__dir__ + '/ProvidersEmailTemplate.html');
        // mail('allanancheta534@gmail.com','TESt Subject', $message,$headers);
        return $this->obj->respondSuccess($response,__dir__+'/' );
    }
}
?>