<?php

declare(strict_types=1);

namespace App\Api\Cpt;

use App\Object\Classroom;
use App\Api\OneSignal\OneSignal as OneSignal;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
// map update
// job_status from working to done to Finish
// payments

final class ComplitingTransaction
{
    private $obj;
    private $json;
    private $signal;
    // $data = $request->getParsedBody();
    function __construct(Classroom $obj, OneSignal $signal)
    {
        $this->obj = $obj;
        $this->signal = $signal;
        $this->json = json_decode(file_get_contents('php://input'));
    }

    public function workingTransaction(Request $request, Response $response, $params): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $status = $params['status'];
            $jobId = (int)$this->obj->sanitizer($this->json->reqId);
            $cliId = (int)$this->obj->sanitizer($this->json->cliId);
            $rId = (int)$this->obj->sanitizer($this->json->rId);
            $validType = $this->checkReqType($params['type']);
            if ($validType) {
                //done sa provider
                if ($status === 'Done' || $status === 'done') {
                    // if($validType[0]->id===1){
                    if ($params['type'] === 'pabili' || $params['type'] === 'Pabili') {
                        // $newStatusPabili = $this->setNewStatusPabili($cliId, "Done", "Working");
                        $newStatusPabili = $this->setNewStatusPabili($jobId, "Done");
                        if ($newStatusPabili) {
                            $message = "Awsome your Pabili Request Status is Done Please Prepare the Payment for your Provider";
                            $this->signal->workingToDoneJob($cliId, $message);
                            return $this->obj->respondSuccess($response, $newStatusPabili);
                        }
                        if (!$newStatusPabili) return $this->obj->respondFailed($response, $newStatusPabili);
                    }
                    if ($params['type'] === 'Padala' || $params['type'] === 'padala') {
                        $newStatusPadala = $this->setNewStatusPadala($jobId, "Done");
                        if ($newStatusPadala) {
                            $message = "Awsome your Padala Request Status is Done Please Prepare the Payment for your Provider";
                            $this->signal->workingToDoneJob($cliId, $message);
                            return $this->obj->respondSuccess($response, $newStatusPadala);
                        }
                        if (!$newStatusPadala) return $this->obj->respondFailed($response, $newStatusPadala);
                    }
                    $newStatusTask = $this->setNewStatusTask($jobId, "Done");
                    if ($newStatusTask) {
                        $message = "Awsome your " . $validType[0]->service_type . " Request Status is Done Please Prepare the Payment for your Provider";
                        $this->signal->workingToDoneJob($cliId, $message);
                        return $this->obj->respondSuccess($response, $newStatusTask);
                    }
                    if (!$newStatusTask) return $this->obj->respondFailed($response, $newStatusTask);
                }
                //ang finish si customer
                if ($status === 'Finish' || $status === 'finish') {
                    $this->setSpAvailability($rId, 1);
                    $this->addClientPoint(0.5, $cliId);
                    $this->addServiceProviderPoint(0.5, $rId);
                    // if($validType[0]->id===1){
                    if ($params['type'] === 'pabili' || $params['type'] === 'Pabili') {
                        // $newStatusPabili = $this->setNewStatusPabili($cliId, "Finish", "Done");
                        $newStatusPabili = $this->setNewStatusPabili($jobId, "Finish");
                        if ($newStatusPabili) {
                            return $this->obj->respondSuccess($response, $newStatusPabili);
                        }
                        if (!$newStatusPabili) return $this->obj->respondFailed($response, $newStatusPabili);
                    }
                    if ($params['type'] === 'Padala' || $params['type'] === 'padala') {
                        $newStatusPadala = $this->setNewStatusPadala($jobId, "Finish");
                        if ($newStatusPadala) {
                            return $this->obj->respondSuccess($response, $newStatusPadala);
                        }
                        if (!$newStatusPadala) return $this->obj->respondFailed($response, $newStatusPadala);
                    }
                    $newStatusTask = $this->setNewStatusTask($jobId, "Finish");
                    if ($newStatusTask) {
                        return $this->obj->respondSuccess($response, $newStatusTask);
                    }
                    if (!$newStatusTask) return $this->obj->respondFailed($response, $newStatusTask);
                }
            }
            if (!$validType) return $this->obj->respondFailed($response, 'INVALID REQUEST TYPE!');
        }
    }

    private function addClientPoint(float $newPoints, int $cliId)
    {
        $points = $this->obj->getData("SELECT rewardspoint from users where id = $cliId");
        $queryPoint = "UPDATE users set rewardspoint = :newPoints where id =:id ";
        $bindData = array(
            ":id" => $cliId,
            ":newPoints" => (float)$points[0]->rewardspoint + $newPoints
        );
        return (int)$this->obj->ExecuteData($queryPoint, $bindData);
    }
    private function addServiceProviderPoint(float $newPoints, int $spId)
    {
        $points = $this->obj->getData("SELECT rewardspoint from providers where id = $spId");
        $queryPoint = "UPDATE providers set rewardspoint = :newPoints where id =:id ";
        $bindData = array(
            ":id" => $spId,
            ":newPoints" => (float)$points[0]->rewardspoint + $newPoints
        );
        return (int)$this->obj->ExecuteData($queryPoint, $bindData);
    }

    private function setNewStatusTask(int $jobId, string $status)
    {
        try {
            $query = "UPDATE job set job_status= :newStatus where id = :jobId ";
            $where = array(
                ":newStatus" => $status,
                ":jobId" => $jobId
            );
            return $this->obj->ExecuteData($query, $where);
        } catch (\Throwable $th) {
            return $th->errorInfo[2];
        }
    }
    private function setNewStatusPadala(int $jobId, string $status)
    {
        try {
            $query = "UPDATE job_padala set job_status= :newStatus where id = :jobId ";
            $where = array(
                ":newStatus" => $status,
                ":jobId" => $jobId
            );
            return $this->obj->ExecuteData($query, $where);
        } catch (\Throwable $th) {
            return $th->errorInfo[2];
        }
    }

     private function setNewStatusPabili(int $job_id, string $status)
    {
        try {
            $query = "UPDATE job_pabili set job_status = :newStatus where id = :job_id ";
                $where = array(
                    ":job_id" => $job_id,
                    ":newStatus" => $status
                );
                return $this->obj->ExecuteData($query, $where);
        } catch (\Throwable $th) {
            return $th->errorInfo[2];
        }
    }

    // private function setNewStatusPabili(int $cliId, string $status, string $oldStatus)
    // {
    //     try {
    //         if ($oldStatus === "Working" || $oldStatus === "working") {
    //             $query = "UPDATE job_pabili set job_status = :newStatus where u_id = :cliId && job_status= :oldStatus ";
    //             $where = array(
    //                 ":oldStatus" => $oldStatus,
    //                 ":cliId" => $cliId,
    //                 ":newStatus" => $status
    //             );
    //             return $this->obj->ExecuteData($query, $where);
    //         }
    //         if ($oldStatus === "Done" || $oldStatus === "done") {
    //             $query = "UPDATE job_pabili set job_status = :newStatus where u_id = :cliId && job_status= :oldStatus ";
    //             $where = array(
    //                 ":oldStatus" => $oldStatus,
    //                 ":cliId" => $cliId,
    //                 ":newStatus" => $status
    //             );
    //             return $this->obj->ExecuteData($query, $where);
    //         }
    //     } catch (\Throwable $th) {
    //         return $th->errorInfo[2];
    //     }
    // }

    private function checkReqType(String $type)
    {
        $query = "SELECT * FROM services where service_type = :service_type";
        $where = array(
            ":service_type" => $type
        );
        $checkRes = $this->obj->getSingleData($query, $where);
        return $checkRes;
    }
    private function checkToken($token)
    {
        $checkToken = $token->getHeader('Authorization')[0];
        if ($checkToken === 'null') return false;
        $isValid = $this->obj->verifyToken($checkToken);
        return $isValid;
    }
    //rider available  = 1
    // rider not available = 0
    private function setSpAvailability(int $spId, int $status)
    {
        try {
            $query = "UPDATE providers set avail = :status where id = :spId ";
            $where = array(
                ":status" => $status,
                ":spId" => $spId
            );
            $result = $this->obj->ExecuteData($query, $where);
            if ($result) return "Updated";
            if (!$result) return "Task Failed Successfully";
        } catch (\Throwable $th) {
            return $th->errorInfo[2];
        }
    }
}
