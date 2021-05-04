<?php

declare(strict_types=1);

namespace App\Api\OneSignal;

use App\Object\Classroom;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class OneSignal
{
  private $obj;
  private $json;
  // $data = $request->getParsedBody();
  function __construct(Classroom $obj)
  {
    $this->obj = $obj;
    $this->json = json_decode(file_get_contents('php://input'));
  }
  private function checkToken($token)
  {
    $checkToken = $token->getHeader('Authorization');
    if (empty($checkToken)) return false;
    // if($checkToken==null || $checkToken==undefined ) return false;
    $isValid = $this->obj->verifyToken($checkToken[0]);
    return $isValid;
  }
  private function noUserFound($response)
  {
    return $this->obj->respondFailed($response, "Invalid User!!!");
  }

  // kapag i add ang user sa database
  public function addId(string $userType, string $playerId, int $userId)
  {
    $addId = "INSERT into playerids value(:playerid,:userId,:userType,current_timestamp()) ";
    $bindData = array(
      ":playerid" => $playerId,
      ":userId" => $userId,
      ":userType" => $userType
    );
    return $this->obj->CreateData($addId, $bindData);
  }

  //kapag mag log in ang user
  public function updateId(string $userType, string $playerId, int $userId)
  {
    $updateId = "UPDATE playerids set playerId = :playerId where userId = :userId  && type = :userType ";
    $bindData = array(
      ":playerId" => $playerId,
      ":userId" => $userId,
      ":userType" => $userType
    );
    return $this->obj->ExecuteData($updateId, $bindData);
  }

  public function getId(int $userId, string $userType)
  {
    $getId = "SELECT * from playerids where userId = :userId && type= :userType ";
    $where = array(
      ":userId" => $userId,
      ":userType" => $userType
    );
    return $this->obj->getSingleData($getId, $where);
  }

  //kapag mag log Out ang user
  public function removeId(Request $request, Response $response, $params): Response
  {
    $tokenCh = $this->checkToken($request);
    if ($tokenCh) {
      $userType = $params['type'];
      $userId = (int)$tokenCh->id;
      $this->updateId($userType, "0", $userId);
      return $this->obj->respondSuccess($response, "Logged out!");
    }
  }

  //
  public function acceptingJob(int $userId, string $message)
  {
    $customerSignalId = $this->getId($userId, 'Customer');
    $data = array();
    return $this->sendNotification($message, [$customerSignalId[0]->playerId], $data);
  }
  public function workingToDoneJob(int $userId, string $message)
  {
    $customerSignalId = $this->getId($userId, 'Customer');
    $data = array();
    return $this->sendNotification($message, [$customerSignalId[0]->playerId], $data);
  }

  public function testSend( Request $request, Response $response, $params): Response{
      $ids=array("a5fa12f4-6346-4aaf-b521-c64e26e9f44a","aea6301f-f700-4a53-9d76-56e339a93991");
      $isSend = $this->sendNotification("testing message",$ids,array());
      return $this->obj->respondSuccess($response, $isSend);
  }

  private function sendNotification(string $message, array $playerIds, array $dataToSend)
  {
    $content = array(
      "en" => $message,
    );
    $fields = array(
      'app_id' => "25ab5cfb-b6bb-4d5b-aa6c-2869a01d321a",
      // 'included_segments' => array('All'),
      // 'include_player_ids' => array('a5fa12f4-6346-4aaf-b521-c64e26e9f44a'),
      'include_player_ids' => $playerIds,
     // 'data' => json_encode($dataToSend),
      'large_icon' => "ic_launcher_round.png",
      'contents' => $content,
    );

    $fields = json_encode($fields);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Content-Type: application/json; charset=utf-8',
      'Authorization: Basic xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
  }

  public function setProviderPlayerId(Request $request, Response $response): Response
  {
    $tokenCh = $this->checkToken($request);
    if ($tokenCh) {
    }
    if (!$tokenCh) return $this->noUserFound($response);
  }

  public function setCustomerPlayerId(Request $request, Response $response): Response
  {
    $tokenCh = $this->checkToken($request);
    if ($tokenCh) {
    }
    if (!$tokenCh) return $this->noUserFound($response);
  }
}
