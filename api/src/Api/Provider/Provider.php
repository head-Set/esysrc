<?php

declare(strict_types=1);

namespace App\Api\Provider;

use App\Object\Classroom;
use App\Api\OneSignal\OneSignal as OneSignal;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class Provider
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
    public function signIn(Request $request, Response $response): Response
    {
        $query = "SELECT * from providers WHERE uname = :uname || email = :email ";
        $where = array(
            ":uname" => $this->obj->sanitizer($this->json->uOe),
            ":email" => $this->obj->sanitizer($this->json->uOe)
        );
        $check = $this->obj->getSingleData($query, $where);
        if ($check) {
            $checkPass = password_verify($this->json->pass, $check[0]->pass);
            if ($checkPass) {
                $tokenData = array(
                    "id" => $check[0]->id,
                    "email" => $check[0]->uname,
                    "role" => 'Provider',
                );
                $token = $this->obj->tokenGenerator($tokenData);
                $this->signal->updateId('Provider', $this->obj->sanitizer($this->json->playerid), (int)$check[0]->id);
                return $this->obj->respondSuccess($response, $token);
            }
            if (!$checkPass) return $this->obj->respondFailed($response, "Wrong Password");
        }
        if (!$check) return $this->obj->respondFailed($response, "No User Found!");
    }

    private function checkToken($token)
    {
        $checkToken = $token->getHeader('Authorization');
        if (empty($checkToken)) return false;
        // if($checkToken==null || $checkToken===undefined ) return false;
        $isValid = $this->obj->verifyToken($checkToken[0]);
        return $isValid;
    }

    //dashboard
    public function rider(Request $request, Response $response): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $checkService = $this->obj->getData("SELECT serviceToProvide,typeDetailes from providers where id=$tokenCh->id");
            $queryArkila = $this->obj->getData("SELECT d_id,prefered,fbId,u_id as cliId, job.id as reqId,service_type as reqType,fname,mname,lname,extension,dateNow as date FROM job join users join services on u_id = users.id && job_type=services.id where job_status = 'Pending' && services.service_type ='Arkila'");
            $queryPadala = $this->obj->getData("SELECT d_id,prefered,fbId,u_id as cliId, job_padala.id as reqId ,fname,mname,lname,extension,dateNow as date FROM job_padala join users on u_id = users.id where job_status = 'Pending'");
            // $queryPabili = $this->obj->getData("SELECT ANY_VALUE(d_id) as d_id, ANY_VALUE(store) as store, ANY_VALUE(prefered) as prefered,fbId,u_id as cliId, ANY_VALUE(job_pabili.id) AS reqId,fname,mname,lname,extension FROM job_pabili,users WHERE u_id = users.id  && job_status = 'Pending' GROUP BY u_id");
            // $queryPabili = $this->obj->getData("SELECT d_id AS d_id, store AS store, prefered AS prefered,fbId,u_id AS cliId, job_pabili.id AS reqId,fname,mname,lname,extension FROM job_pabili,users WHERE u_id = users.id && job_status = 'Pending' GROUP BY dateNow,u_id");
            $queryPabili = $this->obj->getData("SELECT d_id AS d_id, store AS store, prefered AS prefered,fbId,u_id AS cliId, job_pabili.id AS reqId,fname,mname,lname,extension FROM job_pabili,users WHERE u_id = users.id && job_status = 'Pending' ");
            $cleanAP = $cleanPabili = $cleanPadala =  [];
            //arkila
            for ($i = 0; $i < count($queryArkila); $i++) {
                if ($queryArkila[$i]->prefered == $checkService[0]->serviceToProvide) {
                    if ($checkService[0]->serviceToProvide == "Jeep") {
                        $queryCheckMunicipal = $this->obj->getData("SELECT municipal from deliveryaddresses where id = $queryArkila[$i]->d_id");
                        if (stripos($checkService[0]->typeDetailes, $queryCheckMunicipal[0]->municipal)) {
                            array_unshift($cleanAP, $queryArkila[$i]);
                        } else array_unshift($cleanAP, []);
                    } else {
                        array_unshift($cleanAP, $queryArkila[$i]);
                    }
                }
            }
            //pabili
            for ($i = 0; $i < count($queryPabili); $i++) {
                if ($queryPabili[$i]->prefered == $checkService[0]->serviceToProvide) {
                    if ($checkService[0]->serviceToProvide == "Jeep") {
                        $queryCheckMunicipal = $this->obj->getData("SELECT municipal from deliveryaddresses where id = $queryPabili[$i]->d_id");
                        if (stripos($checkService[0]->typeDetailes, $queryCheckMunicipal[0]->municipal)) {
                            array_unshift($cleanPabili, $queryPabili[$i]);
                        } else array_unshift($cleanPabili, []);
                    } else {
                        array_unshift($cleanPabili, $queryPabili[$i]);
                    }
                }
            }
            //padala
            for ($i = 0; $i < count($queryPadala); $i++) {
                if ($queryPadala[$i]->prefered == $checkService[0]->serviceToProvide) {
                    if ($checkService[0]->serviceToProvide == "Jeep") {
                        $queryCheckMunicipal = $this->obj->getData("SELECT municipal from deliveryaddresses where id = $queryPadala[$i]->d_id");
                        if (stripos($checkService[0]->typeDetailes, $queryCheckMunicipal[0]->municipal)) {
                            array_unshift($cleanPadala, $queryPadala[$i]);
                        } else array_unshift($cleanPadala, []);
                    } else {
                        array_unshift($cleanPadala, $queryPadala[$i]);
                    }
                }
            }
            $resp = array(
                "arkila" => $cleanAP,
                "padala" => $cleanPadala,
                "pabili" => $cleanPabili
            );
            return $this->obj->respondSuccess($response, $resp);
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }
    public function atbp(Request $request, Response $response): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $getMyService = $this->obj->getData("SELECT serviceToProvide FROM providers where id = $tokenCh->id");
            $myServices = explode(',', $getMyService[0]->serviceToProvide);

            $query = "SELECT job.id as reqId, job_desc as request, u_id as cliId,fbId,lname,mname,fname,extension,dateNow,d_id,email,contact,prefered FROM job join users join services on u_id = users.id  && job_type=services.id  WHERE job_status = 'Pending' && services.service_type ='At Iba Pa'";
            $result = $this->obj->getData($query);
            if (!$result) return $this->obj->respondFailed($response, "No Data Found");

            if ($result) {
                $data = [];
                foreach ($result as $row) {
                    $deliveryaddress = $this->getAnAddress($row->d_id);
                    $toSearch = explode(',', $row->prefered);
                    foreach ($toSearch as $searchRow) {
                        foreach ($myServices as $myRow) {
                            //hceck ko if match
                            if ($searchRow == $myRow) {
                                array_push($data, array(
                                    "reqId" => $row->reqId,
                                    "request" => $row->request,
                                    "fbId" => $row->fbId,
                                    "cliId" => $row->cliId,
                                    "cliName" => $row->lname . ', ' . $row->fname . ' ' . $row->mname . ' ' . $row->extension,
                                    "date" => $row->dateNow,
                                    "prefered" => $row->prefered,
                                    "deliveryaddress" => $deliveryaddress,
                                ));
                                break 2; // ilang loop sya mag bbreak
                            }
                        }
                    }
                }
                return $this->obj->respondSuccess($response, $data);
            }
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }

    public function workChecker(Request $request, Response $response): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $rId = $tokenCh->id;
            $isWorking = "SELECT avail,rewardsPoint from providers where id = :rId";
            $where = array(
                ":rId" => $rId
                // ":rId"=>2
            );
            $result = $this->obj->getSingleData($isWorking, $where);
            if ($result) {
                if ($result[0]->avail === 1) return $this->obj->respondSuccess($response, "Then Work");
                if ($result[0]->avail === 0) {
                    $pabiliCheck = "SELECT job_pabili.id as reqId,u_id FROM job_pabili WHERE s_provider_id = :rId && job_status != 'Pending' && job_status != 'Finish'  limit 1";
                    $pabiliResult = $this->obj->getSingleData($pabiliCheck, $where);
                    if ($pabiliResult) {
                        $resp = array(
                            "reqId" => $pabiliResult[0]->reqId,
                            "cliId" => $pabiliResult[0]->u_id,
                            "type" => "pabili"
                        );
                        return $this->obj->respondSuccess($response, $resp);
                        exit();
                    }
                    if (!$pabiliResult) {
                        $arkilaCheck = "SELECT job.id as reqId,u_id as cliId, service_type as type from job,services where job_type=services.id && s_provider_id = :rId && job_status != 'Pending' && job_status != 'Finish' ";
                        $arkilaResult = $this->obj->getSingleData($arkilaCheck, $where);
                        if (!empty($arkilaResult[0])) {
                            return $this->obj->respondSuccess($response, $arkilaResult[0]);
                        }
                        if (empty($arkilaResult[0])) {
                            $padalaCheck = "SELECT job_padala.id AS reqId,u_id AS cliId FROM job_padala WHERE s_provider_id = :rId && job_status != 'Pending' && job_status != 'Finish' ";
                            $padalaResult = $this->obj->getSingleData($padalaCheck, $where);
                            $padalaResult[0]->type = 'Padala';
                            return $this->obj->respondSuccess($response, $padalaResult[0]);
                        }
                    }
                }
            }
            if (!$result) return $this->obj->respondFailed($response, "INVALID USER");
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }

    public function jobAccept(Request $request, Response $response, $params): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $jobId =  (int)$this->obj->sanitizer($this->json->reqId); //(int)$params['jobId'];
            $cliId = (int)$this->obj->sanitizer($this->json->cliId); //(int)$params['cliId'];

            $s_provider_id = $tokenCh->id;
            // check ko if valid ung request
            $validType = $this->checkReqType($params['type']);
            if ($validType) {
                // check ko if pending padin ung request
                $isPending = $this->pendingChecker($jobId, $validType[0], $cliId);
                if ($isPending) {
                    $getMe = $this->getMe((int) $s_provider_id);
                    $myName = $getMe[0]->fname . ' ' . $getMe[0]->mname . ' ' . $getMe[0]->lname . ', ' . $getMe[0]->extension;
                    // $myName = $getMe->fname + ' ' + $getMe->mname + ' ' + $getMe->lname + ', ' + $getMe->extension;
                    // if ($validType[0]->id === 1) {
                    if ($params['type'] == 'pabili' || $params['type'] == 'Pabili') {
                        $acceptPabili = $this->acceptPabili($cliId, $s_provider_id);
                        if ($acceptPabili) {
                            $message = "ESY Service Provider:" . $myName . " Accepted your Pabili Request";
                            $this->signal->acceptingJob($cliId, $message);
                            return $this->obj->respondSuccess($response, $acceptPabili);
                        }
                        if (!$acceptPabili) return $this->obj->respondFailed($response, "Something Went Wrong!");
                    }
                    if ($params['type'] == 'padala' || $params['type'] == 'Padala') {
                        $acceptPadala = $this->acceptTask($cliId, true, $jobId, $s_provider_id);
                        if ($acceptPadala) {
                            $message = "ESY Service Provider: " . $myName . " Accepted your " . $validType[0]->service_type . " Request";
                            $this->signal->acceptingJob($cliId, $message);
                            return $this->obj->respondSuccess($response, $acceptPadala);
                        }
                        if (!$acceptPadala) return $this->obj->respondFailed($response, $acceptPadala);
                    }
                    $acceptTask = $this->acceptTask($cliId, false, $jobId, $s_provider_id);
                    if ($acceptTask) {
                        $message = "ESY Service Provider: " . $myName . " Accepted your " . $validType[0]->service_type . " Request";
                        $this->signal->acceptingJob($cliId, $message);
                        return $this->obj->respondSuccess($response, $acceptTask);
                    }
                    if (!$acceptTask) return $this->obj->respondFailed($response, $acceptTask);
                }
                if (!$isPending) return $this->obj->respondFailed($response, "Request Unavailable. It may be Accepted by Others");
            }
            if (!$validType) return $this->obj->respondFailed($response, "INVALID REQUEST TYPE!");
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }
    // padala arkila atbp accept fn
    private function acceptTask(int $cliId, bool $isPadala, int $jobId, int $s_provider_id)
    {
        try {
            $query = "";
            if ($isPadala) {
                $query = "UPDATE job_padala set job_status='Working', s_provider_id = :spId where id = :jobId && u_id = :cliId && job_status = 'Pending' ";
            }
            if (!$isPadala) {
                $query = "UPDATE job set job_status='Working', s_provider_id = :spId where id = :jobId && u_id = :cliId && job_status = 'Pending' ";
            }
            $where = array(
                ":spId" => $s_provider_id,
                ":jobId" => $jobId,
                ":cliId" => $cliId
            );
            $result = $this->obj->ExecuteData($query, $where);
            if ($result) return $this->setSpAvailability($s_provider_id, 0);
            if (!$result) return "Something Went Wrong!";
        } catch (\Throwable $th) {
            return $th->errorInfo[2];
        }
    }
    // pabili accept only
    private function acceptPabili(int $cliId, int $s_provider_id)
    {
        try {
            $query = "UPDATE job_pabili set job_status = 'Working',s_provider_id = :spId where u_id = :cliId && job_status = 'Pending' ";
            $where = array(
                ":spId" => $s_provider_id,
                ":cliId" => $cliId
            );
            $result = $this->obj->ExecuteData($query, $where);
            if ($result) return $this->setSpAvailability($s_provider_id, 0);
            if (!$result) return "Something Went Wrong!";
        } catch (\Throwable $th) {
            return $th->errorInfo[2];
        };
    }
    public function viewPendingRequest(Request $request, Response $response, $params): Response
    {
        $tokenCh = $this->checkToken($request);
        try {
            if ($tokenCh) {
                $type = $params['type'];
                $validType = $this->checkReqType($type);
                if ($validType) {
                    if ($params['type'] === "Pabili" || $params['type'] === "pabili") {
                        // $query = "SELECT users.id AS cliId,ANY_VALUE(job_pabili.id) AS reqId,s_provider_id,lname,mname,fname,extension,store,dateNow,profile,product,qty,job_status as status,prefered,d_id,email,contact FROM job_pabili JOIN users ON u_id = users.id WHERE u_id = :cliId && job_status != 'Finish'";
                        // $query = "SELECT users.id AS cliId,ANY_VALUE(job_pabili.id) AS reqId,s_provider_id,lname,mname,fname,extension,store,dateNow,profile,product,qty,job_status as status,prefered,d_id,email,contact FROM job_pabili JOIN users ON u_id = users.id WHERE u_id = :cliId";
                        // $query = "SELECT users.id AS cliId,job_pabili.id AS reqId,s_provider_id,lname,mname,fname,extension,store,dateNow,profile,product,qty,job_status as status,prefered,d_id,email,contact FROM job_pabili JOIN users ON u_id = users.id WHERE u_id = :cliId && job_status != 'Finish' ";
                        // $query = "SELECT users.id AS cliId,job_pabili.id AS reqId,s_provider_id,lname,mname,fname,extension,store,dateNow,profile,product,qty,job_status as status,prefered,d_id,email,contact FROM job_pabili JOIN users ON u_id = users.id WHERE u_id = :cliId && job_status ='Pending'  ";
                        // $where = array(
                        //     ":cliId" => (int)$params['cliId'],
                        // );
                        // $result = $this->obj->getSingleData($query, $where);
                        // $cliData = $listahan = null;
                        // foreach ($result as $row) {
                        // $cliData = array(
                        //     "cliId" => $row->cliId,
                        //     "cliName" => $row->lname . ', ' . $row->fname . ' ' . $row->mname . ' ' . $row->extension,
                        //     "profile" => $row->profile,
                        //     "store" => $row->store,
                        //     "date" => $row->dateNow,
                        //     "status" => $row->status,
                        //     "prefered" => $row->prefered,
                        //     "contact" => $row->contact,
                        //     "email" => $row->email,
                        //     );
                        //     $listahan[] = array(
                        //         "reqId" => $row->reqId,
                        //         "item" => $row->product,
                        //         "qty" => $row->qty,
                        //     );
                        // };
                        // $deliveryaddress = $this->getAnAddress($result[0]->d_id);
                        // $finalArray = array(
                        //     "clientData" => $cliData,
                        //     "listahan" => $listahan,
                        //     "deliveryaddress" => $deliveryaddress[0]
                        // );
                        // if ($result) return $this->obj->respondSuccess($response, $finalArray);
                        // if (!$result) return $this->obj->respondFailed($response, $result);
                        $query = "SELECT users.id AS cliId,job_pabili.id AS reqId,s_provider_id,lname,mname,fname,extension,store,dateNow,profile,job_status as status,prefered,d_id,email,contact FROM job_pabili JOIN users ON u_id = users.id WHERE u_id = :cliId && job_status ='Pending'  ";
                        $where = array(
                            ":cliId" => (int)$params['cliId'],
                        );
                        $result = $this->obj->getSingleData($query, $where);
                        $query_pabili_items = $this->obj->getData("SELECT * from pabili_items where pabili_id = " . $result[0]->reqId . "");
                        $deliveryaddress = $this->getAnAddress($result[0]->d_id);
                        $cliData = array(
                            "cliId" => $result[0]->cliId,
                            "cliName" => $result[0]->lname . ', ' . $result[0]->fname . ' ' . $result[0]->mname . ' ' . $result[0]->extension,
                            "profile" => $result[0]->profile,
                            "store" => $result[0]->store,
                            "date" => $result[0]->dateNow,
                            "status" => $result[0]->status,
                            "prefered" => $result[0]->prefered,
                            "contact" => $result[0]->contact,
                            "email" => $result[0]->email,
                        );
                        if ($result) return $this->obj->respondSuccess($response, array(
                            "clientData" => $cliData,
                            "listahan" => $query_pabili_items,
                            "deliveryaddress" => $deliveryaddress[0]
                        ));
                        if (!$result) return $this->obj->respondFailed($response, $result);
                        exit();
                    }
                }
            }
        } catch (\Throwable $th) {
            return $this->obj->respondFailed($response, $th);
        }
    }

    public function viewRequest(Request $request, Response $response, $params): Response
    {
        $tokenCh = $this->checkToken($request);
        try {
            if ($tokenCh) {
                $type = $params['type'];
                $validType = $this->checkReqType($type);
                // check if valid ung type
                if ($validType) {
                    // if($validType[0]->id===1){
                    if ($params['type'] === "Pabili" || $params['type'] === "pabili") {
                        // $query = "SELECT users.id AS cliId,ANY_VALUE(job_pabili.id) AS reqId,s_provider_id,lname,mname,fname,extension,store,dateNow,profile,product,qty,job_status as status,prefered,d_id,email,contact FROM job_pabili JOIN users ON u_id = users.id WHERE u_id = :cliId && job_status != 'Finish'";
                        // $query = "SELECT users.id AS cliId,ANY_VALUE(job_pabili.id) AS reqId,s_provider_id,lname,mname,fname,extension,store,dateNow,profile,product,qty,job_status as status,prefered,d_id,email,contact FROM job_pabili JOIN users ON u_id = users.id WHERE u_id = :cliId";
                        // $query = "SELECT users.id AS cliId,job_pabili.id AS reqId,s_provider_id,lname,mname,fname,extension,store,dateNow,profile,product,qty,job_status as status,prefered,d_id,email,contact FROM job_pabili JOIN users ON u_id = users.id WHERE u_id = :cliId && job_status != 'Finish' ";
                        // $query = "SELECT users.id AS cliId,job_pabili.id AS reqId,s_provider_id,lname,mname,fname,extension,store,dateNow,profile,product,qty,job_status as status,prefered,d_id,email,contact FROM job_pabili JOIN users ON u_id = users.id WHERE u_id = :cliId && job_status = 'Working' ||  job_status = 'Done' ";
                        // $where = array(
                        //     ":cliId" => (int)$params['cliId'],
                        // );
                        // $result = $this->obj->getSingleData($query, $where);
                        // $cliData = $listahan = null;
                        // foreach ($result as $row) {
                        //     $cliData = array(
                        //         "cliId" => $row->cliId,
                        //         "cliName" => $row->lname . ', ' . $row->fname . ' ' . $row->mname . ' ' . $row->extension,
                        //         "profile" => $row->profile,
                        //         "store" => $row->store,
                        //         "date" => $row->dateNow,
                        //         "status" => $row->status,
                        //         "prefered" => $row->prefered,
                        //         "contact" => $row->contact,
                        //         "email" => $row->email,
                        //     );
                        //     $listahan[] = array(
                        //         "reqId" => $row->reqId,
                        //         "item" => $row->product,
                        //         "qty" => $row->qty,
                        //     );
                        // };
                        // $deliveryaddress = $this->getAnAddress($result[0]->d_id);
                        // $finalArray = array(
                        //     "clientData" => $cliData,
                        //     "listahan" => $listahan,
                        //     "deliveryaddress" => $deliveryaddress[0]
                        // );
                        // if ($result) return $this->obj->respondSuccess($response, $finalArray);
                        // if (!$result) return $this->obj->respondFailed($response, $result);

                        //  $query = "SELECT users.id AS cliId,job_pabili.id AS reqId,s_provider_id,lname,mname,fname,extension,store,dateNow,profile,job_status as status,prefered,d_id,email,contact FROM job_pabili JOIN users ON u_id = users.id WHERE u_id = :cliId && job_status = 'Working' ||  job_status = 'Done' ";
                        $query = "SELECT users.id AS cliId,job_pabili.id AS reqId,s_provider_id,lname,mname,fname,extension,store,dateNow,profile,job_status as status,prefered,d_id,email,contact FROM job_pabili JOIN users ON u_id = users.id WHERE job_pabili.id = :jobId ";
                        $where = array(
                            // ":cliId" => (int)$params['cliId'],
                            ":jobId" => (int)$params['jobId'],
                        );
                        $result = $this->obj->getSingleData($query, $where);
                        $cliData = array(
                            "reqId" => $result[0]->reqId,
                            "cliId" => $result[0]->cliId,
                            "cliName" => $result[0]->lname . ', ' . $result[0]->fname . ' ' . $result[0]->mname . ' ' . $result[0]->extension,
                            "profile" => $result[0]->profile,
                            "store" => $result[0]->store,
                            "date" => $result[0]->dateNow,
                            "status" => $result[0]->status,
                            "prefered" => $result[0]->prefered,
                            "contact" => $result[0]->contact,
                            "email" => $result[0]->email,
                        );
                        $deliveryaddress = $this->getAnAddress($result[0]->d_id);
                        $query_pabili_items = $this->obj->getData("SELECT * from pabili_items where pabili_id = " . $result[0]->reqId . "");
                        $finalArray = array(
                            "clientData" => $cliData,
                            "listahan" => $query_pabili_items,
                            "deliveryaddress" => $deliveryaddress[0]
                        );
                        if ($result) return $this->obj->respondSuccess($response, $finalArray);
                        if (!$result) return $this->obj->respondFailed($response, $result);
                        // return $this->obj->respondSuccess($response, $result);
                        exit();
                    }
                    if ($params['type'] === "Padala" || $params['type'] === "padala") {
                        $query = "SELECT job_padala.id,receivername,receivercontact,receiveraddress,details,u_id,dateNow as date,job_status as status,lname,mname,fname,extension,profile,prefered,contact,email,d_id FROM job_padala join users on u_id =users.id  where u_id = :cliId && job_padala.id = :jobId ";
                        $where = array(
                            ":cliId" => (int)$params['cliId'],
                            ":jobId" => (int)$params['jobId'],
                        );
                        $result = $this->obj->getSingleData($query, $where);
                        $cliData = array(
                            "cliId" => $result[0]->u_id,
                            "cliName" => $result[0]->lname . ', ' . $result[0]->fname . ' ' . $result[0]->mname . ' ' . $result[0]->extension,
                            "profile" => $result[0]->profile,
                            "prefered" => $result[0]->prefered,
                            "contact" => $result[0]->contact,
                            "email" => $result[0]->email,
                            "status" => $result[0]->status,
                            "reqId" => $result[0]->id,
                        );
                        $requestArr = array(
                            "reqId" => $result[0]->id,
                            "receivername" => $result[0]->receivername,
                            "receivercontact" => $result[0]->receivercontact,
                            "receiveraddress" => $result[0]->receiveraddress,
                            "details" => $result[0]->details,
                            "status" => $result[0]->status,
                        );
                        $deliveryaddress = $this->getAnAddress($result[0]->d_id);
                        $responseArray = array(
                            "clientData" => $cliData,
                            "request" => $requestArr,
                            "deliveryaddress" => $deliveryaddress[0]
                        );
                        return $this->obj->respondSuccess($response, $responseArray);
                    }
                    $query = "SELECT job.id,u_id,service_type as type,dateNow as date,job_desc,job_status as status,lname,mname,fname,extension,profile,prefered,bookingTime,contact,email,d_id FROM job,services,users where job_type = services.id && u_id =users.id && u_id = :cliId && job.id = :jobId && job_type = :jobType ";
                    $where = array(
                        ":cliId" => (int)$params['cliId'],
                        ":jobId" => (int)$params['jobId'],
                        ":jobType" => $validType[0]->id
                    );
                    $result = $this->obj->getSingleData($query, $where);
                    $cliData = array(
                        "cliId" => $result[0]->u_id,
                        "cliName" => $result[0]->lname . ', ' . $result[0]->fname . ' ' . $result[0]->mname . ' ' . $result[0]->extension,
                        "profile" => $result[0]->profile,
                        "prefered" => $result[0]->prefered,
                        "bookingTime" => $result[0]->bookingTime,
                        "contact" => $result[0]->contact,
                        "email" => $result[0]->email,
                        "status" => $result[0]->status,
                        "reqId" => $result[0]->id,
                    );
                    $requestArr = array(
                        "reqId" => $result[0]->id,
                        "request" => $result[0]->job_desc,
                        "type" => $result[0]->type,
                        "status" => $result[0]->status,
                        "date" => $result[0]->date,
                        "prefered" => $result[0]->prefered
                    );
                    $deliveryaddress = $this->getAnAddress($result[0]->d_id);
                    $responseArray = array(
                        "clientData" => $cliData,
                        "request" => $requestArr,
                        "deliveryaddress" => $deliveryaddress[0]
                    );
                    if ($result) return $this->obj->respondSuccess($response, $responseArray);
                    if (!$result) return $this->obj->respondFailed($response, $result);
                    exit();
                    // }
                    // if(!$isPending) return $this->obj->respondFailed($response,"REQUEST UNAVAILABLE");
                }
                if (!$validType) return $this->obj->respondFailed($response, "INVALID REQUEST");
            }
            if (!$tokenCh) return $this->noUserFound($response);
        } catch (\Throwable $th) {
            return $this->obj->respondFailed($response, $th);
        }
    }

    public function profile(Request $request, Response $response, $params): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $result = $this->getMe($tokenCh->id);
            if ($result) return $this->obj->respondSuccess($response, $result[0]);
            if (!$result) return $this->obj->respondFailed($response, $result);
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }
    public function editAcc(Request $request, Response $response, $params): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $contact = $this->obj->sanitizer($this->json->contact);
            $pass = $this->obj->sanitizer($this->json->pass);
            $query = "SELECT id,pass from providers where id = :id ";
            $where = array(
                ":id" => $tokenCh->id
            );
            $check = $this->obj->getSingleData($query, $where);
            if ($check) {
                $checkPass = password_verify($pass, $check[0]->pass);
                if ($checkPass) {
                    // if(empty($uname)){
                    $updateConQuery = "UPDATE providers set contact = :contact where id = :rId";
                    $upWhereCon = array(
                        ":contact" => $contact,
                        ":rId" => $tokenCh->id
                    );
                    $updateCon = $this->obj->ExecuteData($updateConQuery, $upWhereCon);
                    if ($updateCon) return $this->obj->respondSuccess($response, "Contact Updated");
                    if (!$updateCon) return $this->obj->respondFailed($response, 'Something went Wrong');
                    // }
                }
                if (!$checkPass) return $this->obj->respondFailed($response, "INVALID PASSWORD");
            }
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }
    public function changePass(Request $request, Response $response, $params): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $oldpass = $this->obj->sanitizer($this->json->old);
            $newPass = password_hash($this->obj->sanitizer($this->json->new), PASSWORD_BCRYPT);
            $spId = $tokenCh->id;

            $check = "SELECT pass from providers where id = :spId ";
            $where = array(
                ":spId" => $spId
            );
            $result = $this->obj->getSingleData($check, $where);
            if ($result) {
                $checkPass = password_verify($oldpass, $result[0]->pass);
                if ($checkPass) {
                    $updatePass = "UPDATE providers set pass= :newPass where id =:spId ";
                    $wherePass = array(
                        ":newPass" => $newPass,
                        ":spId" => $spId
                    );
                    $isUpdate = $this->obj->ExecuteData($updatePass, $wherePass);
                    if ($isUpdate) return $this->obj->respondSuccess($response, "UPDATED SUCCESSFULLY!");
                    if (!$isUpdate) return $this->obj->respondFailed($response, "OPERATION FAILED SUCCESSFULLY!");
                }
                if (!$checkPass) return $this->obj->respondFailed($response, "PASSWORD INCORRECT");
            }
            if (!$result) return $this->obj->respondFailed($response, "INVALID CREDENTIAL");
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }

    //rewards
    public function getMyPoints(Request $request, Response $response, $params): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $queryPoint = $this->obj->getData("SELECT rewardsPoint from providers where id = $tokenCh->id");
            if ($queryPoint) return $this->obj->respondSuccess($response, $queryPoint[0]->rewardsPoint);
            if (!$queryPoint) return $this->obj->respondFailed($response, $queryPoint);
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }

    public function getAllRewards(Request $request, Response $response): Response
    {
        $queryRewards = $this->obj->getData("SELECT * FROM rewards");
        if ($queryRewards) return $this->obj->respondSuccess($response, $queryRewards);
        if (!$queryRewards) return $this->obj->respondFailed($response, "Something Went Wrong!");
    }
    public function requestToClaim(Request $request, Response $response): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $queryPoint = $this->obj->getData("SELECT rewardsPoint,type from providers where id = $tokenCh->id");
            $myPoints = $queryPoint[0]->rewardsPoint;
            $type = $queryPoint[0]->type;
            $rewardId = $this->obj->sanitizer($this->json->rewardId);
            $qty = $this->obj->sanitizer($this->json->qty);
            $checkReward = $this->obj->getData("SELECT * from rewards where id =$rewardId ");
            if ($checkReward) {
                //quantity check
                if ($checkReward[0]->stock >= $qty) {
                    //total ng points
                    $requiredPoints = $checkReward[0]->points * $qty;
                    if ($myPoints >= $requiredPoints) {
                        //then proceed
                        $queryRequest = "INSERT INTO claimrewards_p values(null,:rewardId,:spId,:type,:qty,current_timestamp(),0)";
                        $bindData = array(
                            ":rewardId" => $rewardId,
                            ":spId" => $tokenCh->id,
                            ":type" => $type,
                            ":qty" => $qty
                        );
                        $insertRequest = $this->obj->CreateData($queryRequest, $bindData);
                        if ($insertRequest)  return $this->obj->respondSuccess($response, $insertRequest);
                        if (!$$insertRequest) return $this->obj->respondFailed($response, "Something went Wrong!");
                    }
                    if ($myPoints < $requiredPoints) return $this->obj->respondFailed($response, "Not Enough Points");
                }
                if ($checkReward[0]->stock !== $qty) return $this->obj->respondFailed($response, "Not Enough Stock");
            }
            if (!$checkReward) return $this->obj->respondFailed($response, "INVALID REWARD ITEM");
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }

    private function getMe(int $providerId)
    {
        $query = "SELECT id,lname,mname,fname,extension,date_join,email,contact,serviceToProvide,rewardspoint,typeDetailes,stars from providers where id =:providerId ";
        $where = array(
            ":providerId" => $providerId
        );
        return $this->obj->getSingleData($query, $where);
    }
    private function resetPoints(int $spId)
    {
        $resetQuery = "UPDATE providers set rewardsPoint = 0 where id = :spId";
        return $this->obj->ExecuteData($resetQuery, [":spId" => $spId]);
    }
    //address
    private function getAnAddress(int $dId)
    {
        $query = "SELECT * FROM deliveryaddresses where id = :dId ";
        $where = array(":dId" => $dId);
        return $this->obj->getSingleData($query, $where);
    }
    private function checkReqType(String $type)
    {
        $query = "SELECT * FROM services where service_type = :service_type";
        $where = array(
            ":service_type" => $type
        );
        $checkRes = $this->obj->getSingleData($query, $where);
        return $checkRes;
    }
    private function pendingChecker(int $jobId, $type, int $cliId)
    {
        if ($type->service_type == 'Pabili') {
            $check = "SELECT job_status from job_pabili where job_status = 'Pending' && u_id = :cliId && id = :jobId";
            $where = array(
                ":jobId" => $jobId,
                ":cliId" => $cliId
            );
            return $this->obj->getSingleData($check, $where);
            exit();
        }
        if ($type->service_type == 'Padala') {
            $check = "SELECT job_status from job_padala where job_status = 'Pending' && u_id = :cliId && id = :jobId";
            $where = array(
                ":jobId" => $jobId,
                ":cliId" => $cliId,
            );
            return $this->obj->getSingleData($check, $where);
        }
        $check = "SELECT job_status from job where job_status = 'Pending' && job_type = :job_type && u_id = :cliId && id = :jobId";
        $where = array(
            ":jobId" => $jobId,
            ":cliId" => $cliId,
            ":job_type" => $type->id
        );
        return $this->obj->getSingleData($check, $where);
    }
    private function noUserFound($response)
    {
        return $this->obj->respondFailed($response, "Invalid User!!!");
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
