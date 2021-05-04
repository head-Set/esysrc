<?php

declare(strict_types=1);

namespace App\Api\Customer;

use App\Object\Classroom;
use App\Api\OneSignal\OneSignal as OneSignal;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// use Slim\Factory\AppFactory;
// use DI\ContainerBuilder;

final class Customer
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
        $check = "SELECT * from users WHERE email = :email && isAdmin = 'Customer' ";
        $where = array(
            ":email" => $this->obj->sanitizer($this->json->uOe)
        );
        $check = $this->obj->getSingleData($check, $where);
        if ($check) {
            // 0 not verified
            // 1 verified
            if ($check[0]->email_verify == 0) return $this->obj->respondFailed($response, "Please Verify Your Email");
            $checkPass = password_verify($this->json->pass, $check[0]->pass);
            if ($checkPass) {
                $tokenData = array(
                    "id" => $check[0]->id,
                    "email" => $check[0]->email,
                    "role" => $check[0]->isAdmin,
                );
                $token = $this->obj->tokenGenerator($tokenData);
                $this->signal->updateId('Customer', $this->obj->sanitizer($this->json->playerid), (int)$check[0]->id);
                return $this->obj->respondSuccess($response, $token);
            }
            if (!$checkPass) return $this->obj->respondFailed($response, "Wrong Password");
        }
        if (!$check) return $this->noUserFound($response);
    }
    public function signUp(Request $request, Response $response): Response
    {

        $check = "SELECT * from users where email = :email && isAdmin = 'Customer' ";
        $where = array(
            ":email" => $this->obj->sanitizer($this->json->email)
        );
        $checkRes = $this->obj->getSingleData($check, $where);
        if (!$checkRes) {
            $pass = $this->obj->sanitizer($this->json->pass);
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $bindData = array(
                ":fname" => ucfirst($this->obj->sanitizer($this->json->fname)),
                ":mname" => ucfirst($this->obj->sanitizer($this->json->mname)),
                ":lname" => ucfirst($this->obj->sanitizer($this->json->lname)),
                ":extension" => ucfirst($this->obj->sanitizer($this->json->extension)),
                ":email" => $this->obj->sanitizer($this->json->email),
                ":gender" => $this->obj->sanitizer($this->json->gender),
                ":pass" => $hash,
                ":contact" => $this->obj->sanitizer($this->json->contact),
                ":birthdate" => substr($this->obj->sanitizer($this->json->birthdate), 0, 10),
            );
            $query = "INSERT into users(
                    fname,
                    mname,
                    lname,
                    extension,
                    email,
                    email_verify,
                    pass,
                    gender,
                    contact,
                    birthdate,
                    date_join,
                    isAdmin
            ) values(
                    :fname,
                    :mname,
                    :lname,
                    :extension,
                    :email,
                    '0',
                    :pass,
                    :gender,
                    :contact,
                    :birthdate,
                    CURDATE(),
                    'Customer'
                )";
            $result = $this->obj->CreateData($query, $bindData);
            $this->signal->addId('Customer', "0", (int)$result);
            $message = "Please Click the link to verify your email https://esy.fusiontechph.com/elCustomer/verifyEmail/" . $result;
            mail($this->obj->sanitizer($this->json->email), "Esy Account Verification", $message);
            return $this->obj->respondSuccess($response, $result);
        }
        if ($checkRes) return $this->obj->respondFailed($response, "Existing");
    }
    private function checkToken($token)
    {
        $checkToken = $token->getHeader('Authorization');
        if (empty($checkToken)) return false;
        // if($checkToken===null || $checkToken===undefined ) return false;
        $isValid = $this->obj->verifyToken($checkToken[0]);
        return $isValid;
    }



    public function dashboard(Request $request, Response $response): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $pabiliQuery = "SELECT id as reqId,job_status as reqStatus,store FROM job_pabili where u_id = :cliId && job_status !='Finish' limit 1 ";
            $arkilaQuery = "SELECT job.id as reqId,job_status as reqStatus,service_type as reqType FROM job join services on job.job_type = services.id where services.service_type = 'Arkila'  && u_id = :cliId && job_status !='Finish' ";
            $padalaQuery = "SELECT id as reqId,job_status as reqStatus FROM job_padala where u_id = :cliId && job_status !='Finish' ";
            $atbpQuery =  "SELECT job.id as reqId,job_status as reqStatus,service_type as reqType FROM job join services on job.job_type = services.id where services.service_type = 'At Iba Pa'  && u_id = :cliId && job_status !='Finish' ";
            $where = array(
                ":cliId" => $tokenCh->id,
            );
            $resPabili = $this->obj->getSingleData($pabiliQuery, $where);
            $resArkila = $this->obj->getSingleData($arkilaQuery, $where);
            $resPadala = $this->obj->getSingleData($padalaQuery, $where);
            $resAtbp = $this->obj->getSingleData($atbpQuery, $where);
            $resp = array(
                "Arkila" => $resArkila,
                "Padala" => $resPadala,
                "Atbp" => $resAtbp,
                "Pabili" => $resPabili
            );
            return $this->obj->respondSuccess($response, $resp);
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }

    //$time = date("H:i:s a");
    //pabili only
    public function insertPabiii(Request $request, Response $response): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $item = $this->json->product;
            $qty = $this->json->prodQty;
            $vehicleType = $this->obj->sanitizer($this->json->vehicleType);
            $checkReqType = $this->checkReqType("Pabili");
            $isPending = $this->totalPendingChecker($tokenCh->id, $checkReqType[0]);
            if ($isPending[0]->totalPending) {
                return $this->obj->respondFailed($response, "One Pabili Request Only At A Time");
                exit();
            }
            try {
                $query = "INSERT INTO job_pabili(u_id,store,prefered,d_id) values(:cliId,:store,:prefered,:dId)";
                     $bindData = array(
                        ":cliId" => (int)$tokenCh->id,
                        ":store" => $this->obj->sanitizer($this->json->store),
                        ":prefered" => $vehicleType,
                        ":dId" => $this->obj->sanitizer($this->json->dId),
                    );
                    $result = $this->obj->CreateData($query, $bindData);


                for ($i = 0; $i < count($item); $i++) {
                    $query_pabili_items = "INSERT INTO pabili_items(pabili_id,product,qty) values(:pabili_id,:product,:qty)";
                    $bindData_pabili_items = array(
                        ":pabili_id" => (int)$result,
                        ":product" => $item[$i],
                        ":qty" => (int)$qty[$i],
                    );
                    // $_pabili_items_result = $this->obj->CreateData($query_pabili_items, $bindData_pabili_items);
                    $this->obj->CreateData($query_pabili_items, $bindData_pabili_items);
                }
                return $this->obj->respondSuccess($response, $result);
            } catch (\Throwable $th) {
                return $this->obj->respondFailed($response, $th->errorInfo[2]);
            }
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }

    //arkila padala atbp
    public function reqTask(Request $request, Response $response): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $isRequestType = $this->obj->sanitizer($this->json->reqType);
            $checkReqType = $this->checkReqType($isRequestType);
            if ($checkReqType) {

                // $insertTask = $this->insertTask($checkReqType[0]->id, $tokenCh->id);
                $insertTask = $this->insertTask($checkReqType[0], $tokenCh->id);
                if ($insertTask) return $this->obj->respondSuccess($response, $insertTask);
                if (!$insertTask) return $this->obj->respondFailed($response, "Your Only Allowed 1 Request at a time for this Request");
            }
            if (!$checkReqType) return $this->obj->respondfailed($response, "Invalid Request Type!");
        }
        if (!$tokenCh) return  $this->noUserFound($response);
    }
    private function insertTask(Object $type, int $cliId)
    {
        try {
            $checkPending = $this->totalPendingChecker($cliId, $type);
            if (!$checkPending[0]->totalPending) {
                $isBooking = $this->obj->sanitizer($this->json->isBooking);
                if (!$isBooking) {
                    if ($type->service_type == 'At Iba Pa' || $type->service_type == 'At Iba pa') {
                        $query = "INSERT INTO job (u_id,job_type,job_desc,prefered,d_id) values(:u_id,:job_type,:job_desc,:prefered,:dId)";
                        $bindData = array(
                            ":u_id" => $cliId,
                            ":job_type" => $type->id,
                            ":job_desc" => $this->obj->sanitizer($this->json->requestTask),
                            ":prefered" => $this->obj->sanitizer($this->json->subServices),
                            ":dId" => $this->obj->sanitizer($this->json->dId),
                        );
                        return $this->obj->CreateData($query, $bindData);
                    }
                    if ($type->service_type == 'Padala') {
                        $query = "INSERT INTO job_padala(u_id,receivername,receivercontact,receiveraddress,details,prefered,d_id) values(:u_id,:receivername,:receivercontact,:receiveraddress,:details,:prefered,:d_id)";
                        $bindData = array(
                            ":u_id" => $cliId,
                            ":receivername" => $this->obj->sanitizer($this->json->parcelDetail->name),
                            ":receivercontact" => (int)$this->obj->sanitizer($this->json->parcelDetail->contact),
                            ":receiveraddress" => $this->obj->sanitizer($this->json->parcelDetail->add),
                            ":details" => $this->obj->sanitizer($this->json->parcelDetail->details),
                            ":prefered" => $this->obj->sanitizer($this->json->vehicleType),
                            ":d_id" => $this->obj->sanitizer($this->json->dId),
                        );
                        return $this->obj->CreateData($query, $bindData);
                        // return $this->json->parcelDetail;
                    }
                    //arkila
                    $query = "INSERT INTO job(u_id,job_type,job_desc,prefered,d_id)values(:u_id,:job_type,:job_desc,:prefered,:dId)";
                    $bindData = array(
                        ":u_id" => $cliId,
                        ":job_type" => $type->id,
                        ":job_desc" => $this->obj->sanitizer($this->json->requestTask),
                        ":prefered" => $this->obj->sanitizer($this->json->vehicleType),
                        ":dId" => $this->obj->sanitizer($this->json->dId),
                    );
                    return $this->obj->CreateData($query, $bindData);
                }
                if ($isBooking) {
                    $query = "INSERT INTO job(u_id,job_type,job_desc,dateNow,prefered,bookingTime,d_id)values(:u_id,:job_type,:job_desc,CURDATE(), :prefered, :isBooking,:dId)";
                    $bindData = array(
                        ":u_id" => $cliId,
                        ":job_type" => $type->id,
                        ":job_desc" => $this->obj->sanitizer($this->json->requestTask),
                        ":prefered" => $this->obj->sanitizer($this->json->vehicleType),
                        ":isBooking" => $this->obj->sanitizer($this->json->time),
                        ":dId" => $this->obj->sanitizer($this->json->dId),
                    );
                    return $this->obj->CreateData($query, $bindData);
                }
            }if ($checkPending[0]->totalPending) {
                return false;
            }
            return false;
        } catch (\Throwable $th) {
            return $th;
        }
    }

    //view and delete request
    public function processTask(Request $request, Response $response, $params): Response
    {
        try {
            $tokenCh = $this->checkToken($request);
            if ($tokenCh) {
                $reqMethod = $request->getMethod();
                $isType = $this->checkReqType($params['type']);
                $cliId = $tokenCh->id;
                if ($isType) {
                    if ($reqMethod === "DELETE") {
                        $isPending = $this->pendingChecker((int)$params['id'], $isType[0], $cliId);
                        if ($isPending) {
                            $deleteReq = $this->deleteReq((int)$params['id'], $cliId, $isType);
                            if ($deleteReq) return $this->obj->respondSuccess($response, $deleteReq);
                            if (!$deleteReq) return $this->obj->respondFailed($response, $deleteReq);
                        }
                        if (!$isPending) return $this->obj->respondSuccess($response, "Request is Already in Progress. Cannot be Deleted!");
                    }
                    if ($reqMethod === "GET" || $reqMethod === "POST") {
                        $viewReq = $this->viewReq((int)$params['id'], $cliId, $isType);
                        if ($viewReq) return $this->obj->respondSuccess($response, $viewReq);
                        if (!$viewReq) return $this->obj->respondFailed($response, "No Data Found!");
                    }
                }
                if (!$isType) return $this->obj->respondFailed($response, "Invalid Request Type!");
            }
            if (!$tokenCh) return $this->noUserFound($response);
        } catch (\Throwable $th) {
            // return $this->obj->respondFailed($response,"Something Went Wrong!");
            return $this->obj->respondFailed($response, $th);
        }
    }
    public function deleteITem(Request $request, Response $response, $params): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $rId = $params['rId'];
            $cliId = $tokenCh->id;
            $isPending = $this->pendingChecker((int)$params['id'], array("service_type" => "Pabili"), $cliId);
            if ($isPending) {
            }
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }
    //any job_Status
    private function viewReq(int $jobId, int $cliId, array $isType)
    {
        try {
            if ($isType[0]->service_type === "Pabili" || $isType[0]->service_type === "pabili") {
                $query = "SELECT u_id as cliId,dateNow as date,job_pabili.id as itemId,job_status as status,s_provider_id as rId,store,d_id,prefered as vehicleType FROM job_pabili where u_id = :cliId && job_status !='Finish' ";
                $where = array(
                    ":cliId" => $cliId
                );
                $result = $this->obj->getSingleData($query, $where);
                $deliveryAdd = $this->getAnAddress($result[0]->d_id);
                $pabili_items = $this->obj->getData("SELECT * from pabili_items where pabili_id = ".$result[0]->itemId." ");
                return array(
                    "prod"=>$pabili_items,
                    "data"=>$result[0]
                );
                // $query = "SELECT u_id,dateNow,job_pabili.id as itemId,job_status,product,qty,s_provider_id as spId,store,d_id,prefered FROM job_pabili where u_id = :cliId && job_status !='Finish' ";
                // $where = array(
                //     ":cliId" => $cliId,
                // );
                // $result = $this->obj->getSingleData($query, $where);
                // $deliveryAdd = $this->getAnAddress($result[0]->d_id);
                // $otherData = $listahan = [];
                // foreach ($result as $row) {
                //     $prod[] = array(
                //         "id" => $row->itemId,
                //         "item" => $row->product
                //     );
                //     $qty[] = $row->qty;
                //     $testArray = array(
                //         "store" => $row->store,
                //         "qty" => $qty,
                //         "product" => $prod,
                //         "vehicleType" => $row->prefered,
                //         "date" => $row->dateNow,
                //         "status" => $row->job_status,
                //         "rId" => $row->spId,
                //         "deliveryAddress" => $result[0]->d_id,
                //         "cliId" => $cliId,
                //     );
                // };
                // return $testArray;
                // exit();
            }
            if ($isType[0]->service_type === "Padala" || $isType[0]->service_type === "padala") {
                $query = "SELECT * from job_padala where id = :jobId";
                $where = array(
                    ":jobId" => $jobId,

                );
                $result = $this->obj->getSingleData($query, $where);
                return $result[0];
                exit();
            }

            $query = "SELECT u_id,job.id as reqId,job_status,job_desc,service_type as type,s_provider_id as r_id,d_id as deliveryAddress,dateNow,prefered from job,services  where job.job_type = services.id && job.id = :jobId ";
            $where = array(
                ":jobId" => $jobId,

            );
            $result = $this->obj->getSingleData($query, $where);

            return $result;
            exit();
        } catch (\Throwable $th) {
            return $th;
        }
    }
    private function deleteReq(int $jobId, int $cliId, array $isType)
    {
        try {
            if ($isType[0]->service_type === "Pabili" || $isType[0]->service_type === "pabili") {
                $query = "DELETE from job_pabili where u_id = :cliId && job_status = 'Pending' ";
                $where = array(
                    ":cliId" => $cliId
                );
                return $this->obj->ExecuteData($query, $where);
                exit();
            }
            if ($isType[0]->service_type === "Padala" || $isType[0]->service_type === "padala") {
                $query = "DELETE FROM job_padala where id = :jobId && u_id = :cliId";
                $where = array(
                    ":jobId" => $jobId,
                    ":cliId" => $cliId
                );
                return $this->obj->ExecuteData($query, $where);
                exit();
            }
            $query = "DELETE FROM job where id = :jobId && u_id = :cliId";
            $where = array(
                ":jobId" => $jobId,
                ":cliId" => $cliId
            );
            return $this->obj->ExecuteData($query, $where);
            exit();
        } catch (\Throwable $th) {
            return $th->errorInfo[2];
        }
    }

    public function profile(Request $request, Response $response, $params): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $cliId = $tokenCh->id;
            $query = "SELECT * from users where id =:cliId ";
            $where = array(
                ":cliId" => $cliId
            );
            $result = $this->obj->getSingleData($query, $where);;
            if ($result) return $this->obj->respondSuccess($response, $result[0]);
            if (!$result) return $this->obj->respondFailed($response, $result);
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }

    public function getMyProvider(Request $request, Response $response, $params): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $cliId = $tokenCh->id;
            $spId = (int)$params['spId'];
            $query = "SELECT fname,mname,lname,extension,contact,date_join,stars,fbId,email,serviceToProvide,typeDetailes from providers where id = :spId";
            $where = array(
                ":spId" => $spId
            );
            $result = $this->obj->getSingleData($query, $where);
            $finalArray = array(
                "name" => $result[0]->lname . ', ' . $result[0]->fname . ' ' . $result[0]->mname . ' ' . $result[0]->extension,
                "contact" => $result[0]->contact,
                "fbId" => $result[0]->fbId,
                "stars" => $result[0]->stars,
                "date_join" => $result[0]->date_join,
                "email" => $result[0]->email,
                "serviceToProvide" => $result[0]->serviceToProvide,
                "typeDetailes" => $result[0]->typeDetailes
            );
            return $this->obj->respondSuccess($response, $finalArray);
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }

    public function addAddress(Request $request, Response $response): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $cliId = $tokenCh->id;
            $street = $this->obj->sanitizer($this->json->street);
            $houseNo = $this->obj->sanitizer($this->json->houseNo);
            $brgy = $this->obj->sanitizer($this->json->barangay);
            $municipal = $this->obj->sanitizer($this->json->municipal);
            $province = $this->obj->sanitizer($this->json->province);
            $zipCode = $this->obj->sanitizer($this->json->zipCode);
            $moreDetail = $this->obj->sanitizer($this->json->moreDetail);

            $queryAddress = "INSERT INTO deliveryaddresses values(null,:u_id,:street,:houseNo,:brgy,:municipal,:province,:zipCode,:moreDetail)";
            $bindData = array(
                ":u_id" => $cliId,
                ":street" => $street,
                ":houseNo" => $houseNo,
                ":brgy" => $brgy,
                ":municipal" => $municipal,
                ":province" => $province,
                ":zipCode" => $zipCode,
                ":moreDetail" => $moreDetail,
            );
            $insertAddress = $this->obj->CreateData($queryAddress, $bindData);
            if ($insertAddress) return $this->obj->respondSuccess($response, $insertAddress);
            if (!$insertAddress) return $this->obj->respondFailed($response, "Failed To Add");
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }

    public function getMyAddresses(Request $request, Response $response): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $cliId = $tokenCh->id;
            $checkAddress = "SELECT * FROM deliveryaddresses where u_id = :cliId";
            $where = array(":cliId" => $cliId);
            $result = $this->obj->getSingleData($checkAddress, $where);
            if ($result) return $this->obj->respondSuccess($response, $result);
            if (!$result) return $this->obj->respondFailed($response, "No Data Found");
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }


    //feedback
    public function writeFeedback(Request $request, Response $response, $params): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            try {
                $type = $params['type'];
                $isValidType = $this->checkReqType($type);
                if ($isValidType) {
                    $reqId = $this->obj->sanitizer($this->json->reqId);
                    $spId = $this->obj->sanitizer($this->json->spId);
                    $cliId = $tokenCh->id;
                    $stars = $this->obj->sanitizer($this->json->starsGiven);
                    $feedBack = $this->obj->sanitizer($this->json->feedback);
                    $queryInsertFeedBack = null;
                    if ($isValidType[0]->id === 1) {
                        $queryInsertFeedBack = "INSERT into feedback_pabili values(null,:reqId,:type,:spId,:cliId,:feedback)";
                    } else {
                        $queryInsertFeedBack = "INSERT into feedback_job values(null,:reqId,:type,:spId,:cliId,:feedback)";
                    }
                    $bindData = array(
                        ":reqId" => $reqId,
                        ":type" => $isValidType[0]->id,
                        ":spId" => $spId,
                        ":cliId" => $cliId,
                        ":feedback" => $feedBack
                    );
                    $insertFeedBack = $this->obj->CreateData($queryInsertFeedBack, $bindData);
                    if ($insertFeedBack) {
                        $this->addStars((int)$stars, (int) $spId);
                        $insertReward = $this->addRewardPoint(0.2, $cliId);
                        if ($insertReward) return $this->obj->respondSuccess($response, $insertFeedBack);
                        if (!$insertReward) return $this->obj->respondFailed($response, $insertReward);
                    }
                    if (!$insertFeedBack) return $this->obj->respondFailed($response, $insertFeedBack);
                }
                if (!$isValidType) return $this->obj->respondFailed($response, "Invalid Type!");
            } catch (\Throwable $th) {
                return $this->obj->respondFailed($response, $th);
            }
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }

    //reward points
    public function getMyPoints(Request $request, Response $response): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $queryPoint = $this->obj->geTData("SELECT rewardsPoint from users where id = $tokenCh->id");
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
            $queryPoint = $this->obj->getData("SELECT rewardsPoint from users where id = $tokenCh->id");
            $myPoints = $queryPoint[0]->rewardsPoint;
            $rewardId = $this->obj->sanitizer($this->json->rewardId);
            $qty = $this->obj->sanitizer($this->json->qty);
            $d_id = $this->obj->sanitizer($this->json->d_id);
            $checkReward = $this->obj->getData("SELECT * from rewards where id =$rewardId ");
            if ($checkReward) {
                //quantity check
                if ($checkReward[0]->stock >= $qty) {
                    //total ng points
                    $requiredPoints = $checkReward[0]->points * $qty;
                    if ($myPoints >= $requiredPoints) {
                        //then proceed
                        $queryRequest = "INSERT INTO claimrewards_c values(null,:rewardId,:cliId,:qty,:d_id,current_timestamp(),0)";
                        $bindData = array(
                            ":rewardId" => $rewardId,
                            ":cliId" => $tokenCh->id,
                            ":qty" => $qty,
                            ":d_id" => $d_id
                        );
                        $insertRequest = $this->obj->CreateData($queryRequest, $bindData);
                        if ($insertRequest) {
                            $newPoints = $myPoints - $requiredPoints;
                            $queryUpdatePoints = "UPDATE users set rewardsPoint = :newPoints where id = :cliId ";
                            $pointsBindData = array(
                                ":newPoints" => $newPoints,
                                ":cliId" => $tokenCh->id
                            );
                            $updatePoints = $this->obj->ExecuteData($queryUpdatePoints, $pointsBindData);
                            if ($updatePoints) {
                                $newStock = $checkReward[0]->stock - $qty;
                                $queryUpdateStock = "UPDATE rewards set stock = :newStock where id = :rId ";
                                $stockBindData = array(
                                    ":newStock" => $newStock,
                                    ":rId" => $checkReward[0]->id
                                );
                                $updateStock = $this->obj->ExecuteData($queryUpdateStock, $stockBindData);
                                if ($updateStock) return $this->obj->respondSuccess($response, $insertRequest);
                                if (!$updateStock) return $this->obj->respondSuccess($response, "Updating Stock Failed");
                            }
                            if (!$updatePoints) return $this->obj->respondSuccess($response, "Updating Points Failed");
                        }
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

    //edit account
    public function editAccount(Request $request, Response $response): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $contact = $this->obj->sanitizer($this->json->contact);
            $pass = $this->obj->sanitizer($this->json->pass);
            $query = "SELECT id,pass from users where id = :id ";
            $where = array(
                ":id" => $tokenCh->id
            );
            $check = $this->obj->getSingleData($query, $where);
            if ($check) {
                $checkPass = password_verify($pass, $check[0]->pass);
                if ($checkPass) {
                    // if(empty($uname)){
                    $updateConQuery = "UPDATE users set contact = :contact where id = :cliId";
                    $upWhereCon = array(
                        ":contact" => $contact,
                        ":cliId" => $tokenCh->id
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
            $cliId = $tokenCh->id;

            $check = "SELECT pass from users where id = :cliId ";
            $where = array(
                ":cliId" => $cliId
            );
            $result = $this->obj->getSingleData($check, $where);
            if ($result) {
                $checkPass = password_verify($oldpass, $result[0]->pass);
                if ($checkPass) {
                    $updatePass = "UPDATE users set pass= :newPass where id =:cliId ";
                    $wherePass = array(
                        ":newPass" => $newPass,
                        ":cliId" => $cliId
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

    public function verifyEmail(Request $request, Response $response, $params): Response
    {
        $cliId = $params['id'];
        $isExist = $this->obj->getData("SELECT email FROM users where id = $cliId");
        if ($isExist) {
            $verifyEmail = "UPDATE users set email_verify = 1 where id = :cliId";
            $bindData = [
                ":cliId" => $cliId
            ];
            $result = $this->obj->ExecuteData($verifyEmail, $bindData);
            if ($result) $response->getBody()->write('Email Verified Please Login Your Account');
            if (!$result) $response->getBody()->write('Email Verification Failed');
            return $response;
        }
        if (!$isExist) return $this->noUserFound($response);
    }

    public function getSubServices(Request $request, Response $response, $params): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $check = "SELECT * from services where service_type = :service_type";
            $where = array(
                // ":service_type" => $this->obj->sanitizer($this->json->service_type)
                ":service_type" => $params['service_type']
            );
            $result = $this->obj->getSingleData($check, $where);
            if (!empty($result)) {
                $getSubServies = "SELECT * from subservices where typeid = :typeid";
                $subServicesWhere = array(
                    ":typeid" => $result[0]->id
                );
                $subServicesResult = $this->obj->getSingleData($getSubServies, $subServicesWhere);
                if (!empty($subServicesResult)) return $this->obj->respondSuccess($response, $subServicesResult);
                if (empty($subServicesResult)) return $this->obj->respondFailed($response, "Np Data Found!");
            }
            if (empty($result)) return $this->obj->respondFailed($response, "Invalid Service Type!");
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }
    //function helpers
    private function addStars(int $additionalStar, int $spId)
    {
        $checkStars = "SELECT stars from providers where id = $spId";
        $star = $this->obj->getData($checkStars);
        $queryStar = "UPDATE providers set stars =:starsGiven where id = :spId ";
        $bindData = array(
            ":starsGiven" => (int)$star[0]->stars + $additionalStar,
            ":spId" => $spId
        );
        return $this->obj->ExecuteData($queryStar, $bindData);
    }
    private function addRewardPoint(float $newPoints, int $cliId)
    {
        $checkPoints = "SELECT rewardspoint from users where id = $cliId";
        $points = $this->obj->getData($checkPoints);
        $queryPoint = "UPDATE users set rewardspoint = :newPoints where id =:id ";
        $bindData = array(
            ":id" => $cliId,
            ":newPoints" => (float)$points[0]->rewardspoint + $newPoints
        );
        return (int)$this->obj->ExecuteData($queryPoint, $bindData);
    }
    private function noUserFound($response)
    {
        return $this->obj->respondFailed($response, "Invalid User!!!");
    }
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
    private function totalPendingChecker(int $cliId, Object $type)
    {
        try {
            if ($type->service_type == 'Pabili') {
                $check = "SELECT count(job_status) as totalPending from job_pabili where u_id = :cliId && job_status = 'Pending' ||  job_status = 'Working' ";
                $where = array(
                    ":cliId" => $cliId,
                );
                return $this->obj->getSingleData($check, $where);
                exit();
            }
            if ($type->service_type == 'Padala') {
                $check = "SELECT count(job_status) as totalPending from job_padala where u_id = :cliId && job_status = 'Pending' ||  job_status = 'Working' ";
                $where = array(
                    ":cliId" => $cliId,
                );
                return $this->obj->getSingleData($check, $where);
                exit();
            }

            $check = "SELECT count(job_status) as totalPending from job where job_type = :job_type && u_id = :cliId && job_status = 'Pending' ||  job_status = 'Working'  ";
            $where = array(
                ":cliId" => $cliId,
                ":job_type" => $type->id
            );
            return $this->obj->getSingleData($check, $where);
        } catch (\Throwable $th) {
            return "Something Went Wrong!";
        }
    }
    private function pendingChecker(int $jobId, $type, int $cliId)
    {
        if ($type->service_type == 'Pabili') {
            $check = "SELECT job_status from job_pabili where job_status = 'Pending' && u_id = :cliId && id = :jobId";
            $where = array(
                ":jobId" => $jobId,
                ":cliId" => $cliId,
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
            exit();
        }
        $check = "SELECT job_status from job where job_status = 'Pending' && job_type = :job_type && u_id = :cliId && id = :jobId";
        $where = array(
            ":jobId" => $jobId,
            ":cliId" => $cliId,
            ":job_type" => $type->id
        );
        return $this->obj->getSingleData($check, $where);
    }
}
