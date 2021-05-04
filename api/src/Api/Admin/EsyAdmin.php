<?php

declare(strict_types=1);

namespace App\Api\Admin;

use App\Object\Classroom;
use App\Api\OneSignal\OneSignal as OneSignal;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class EsyAdmin
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
    public function signIn(Request $request, Response $response): Response
    {
        $check = "SELECT * from users WHERE email = :email && isAdmin != 'Customer'  ";
        $where = array(
            ":email" => $this->obj->sanitizer($this->json->uOe)
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
    }
    public function addAdmin(Request $request, Response $response): Response
    {
        $check = "SELECT * from users where email = :email && isAdmin = 'Admin' ";
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
                ":pass" => $hash,
                ":contact" => $this->obj->sanitizer($this->json->contact),
                ":birthdate" => $this->obj->sanitizer($this->json->birthdate),
            );
            $query = "INSERT into users(
                    fname,
                    mname,
                    lname,
                    extension,
                    email,
                    pass,
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
                    :pass,
                    :contact,
                    :birthdate,
                    CURDATE(),
                    'Admin'
                )";
            $result = $this->obj->CreateData($query, $bindData);
            $this->signal->addId('Admin', "0", (int)$result);
            $message = "'Your Request to Join ESY Team is Approved By the Admin, your Password: '" . $this->obj->sanitizer($this->json->pass) . "', you can change the provided password as you want, once your First Logging in is Successfull Thank You. '";
            mail($this->obj->sanitizer($this->json->email), "Esy Account Verification", $message);
            return $this->obj->respondSuccess($response, $result);
        }
        if ($checkRes) return $this->obj->respondFailed($response, "Existing");
    }
    public function getAdmins(Request $request, Response $response): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $getAdmins = $this->obj->getData("SELECT fname,lname,mname,extension,email,contact,isAdmin,date_join from users where isAdmin !='Customer'");
            return $this->obj->respondSuccess($response, $getAdmins);
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }
    public function index(Request $request, Response $response): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $atbp = $this->obj->getData("SELECT count(type)as atbp from providers where type='At Iba Pa'");
            $rider = $this->obj->getData("SELECT count(type) as rider from providers where type='Rider'");
            $services = $this->obj->getData("SELECT COUNT(service_type) as services FROM services");

            // $pabiliRequest = $this->obj->getData("SELECT distinct dateNow from job_pabili ");
            $pabiliRequest = $this->obj->getData("SELECT * from job_pabili ");
            $arkilaRequest = $this->obj->getData("SELECT u_id,job_status from job join services on job_type=services.id where service_type = 'Arkila' ");
            $padalaRequest = $this->obj->getData("SELECT u_id,job_status from job_padala ");
            $atbpRequest = $this->obj->getData("SELECT u_id,job_status from job join services on job_type=services.id where service_type = 'At Iba Pa' ");

            $customerCount = $this->obj->getData("SELECT * FROM users where isAdmin = 'Customer'");
            // $claiming = $this->obj->getData("SELECT COUNT(isApproved) as requests FROM claimrewards where isApproved = 0");
            //coount ni pabili, padala, arkila, atbp, services
            $resp = array(
                "atbp" => $atbp[0]->atbp,
                "rider" => $rider[0]->rider,
                "services" => $services[0]->services,
                // "pabiliRequest" => count($pabiliRequest),
                "pabiliRequest" => $pabiliRequest,
                "arkilaRequest" => $arkilaRequest,
                "padalaRequest" => $padalaRequest,
                "atbpRequest" => $atbpRequest,
                "customer" => count($customerCount)
                // "claimingCount"=>$claiming[0]->requests,

            );

            return $this->obj->respondSuccess($response, $resp);
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }
    //providers
    public function getAllProviders(Request $request, Response $response): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $query = $this->obj->getData("SELECT * From providers where type ='" . $this->json->pType . "' ");
            if (!$query) {
                return $this->obj->respondFailed($response, "No Data Found!");
            }
            if ($query) {
                foreach ($query as $row) {
                    $resp[] = array(
                        "id" => $row->id,
                        "name" => $row->lname . ', ' . $row->fname . ', ' . $row->mname,
                        "email" => $row->email,
                        "dateJoined" => $row->date_join,
                        "prof" => '',
                    );
                }
                return $this->obj->respondSuccess($response, $resp);
            }
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }
    public function addProvider(Request $request, Response $response): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            if ($this->obj->sanitizer($this->json->vTypeDetailes) !== "N/A") {
                $check = "SELECT * from providers where email = :email || typeDetailes = :typeDetailes ";
                $where = array(
                    ":email" => $this->obj->sanitizer($this->json->email),
                    ":typeDetailes" => $this->obj->sanitizer($this->json->vTypeDetailes)
                );
            } else {
                $check = "SELECT * from providers where email = :email ";
                $where = array(
                    ":email" => $this->obj->sanitizer($this->json->email),
                );
            }
            $checkRes = $this->obj->getSingleData($check, $where);

            if ($checkRes) {
                return $this->obj->respondFailed($response, "Existing");
            }
            if (!$checkRes) {
                $hash = password_hash($this->obj->sanitizer($this->json->pass), PASSWORD_BCRYPT);
                $bindData = array(
                    ":fname" => ucfirst($this->obj->sanitizer($this->json->fname)),
                    ":mname" => ucfirst($this->obj->sanitizer($this->json->mname)),
                    ":lname" => ucfirst($this->obj->sanitizer($this->json->lname)),
                    ":extension" => ucfirst($this->obj->sanitizer($this->json->extension)),
                    ":email" => $this->obj->sanitizer($this->json->email),
                    ":pass" => $hash,
                    ":pAdd" => $this->obj->sanitizer($this->json->pAdd),
                    ":rAdd" => $this->obj->sanitizer($this->json->rAdd),
                    ":contact" => $this->obj->sanitizer($this->json->contact),
                    ":birthdate" => $this->obj->sanitizer($this->json->birthdate),
                    ":type" => $this->obj->sanitizer($this->json->type),
                    // ":date_join"=>'CURDATE()'
                    ":serviceToProvide" => $this->obj->sanitizer($this->json->vType),
                    ":typeDetailes" => $this->obj->sanitizer($this->json->vTypeDetailes),
                );
                $query = "INSERT into providers(
                        fname,
                        mname,
                        lname,
                        extension,
                        email,
                        pass,
                        pAdd,
                        rAdd,
                        contact,
                        birthdate,
                        type,
                        date_join,
                        serviceToProvide,
                        typeDetailes
                ) values(
                        :fname,
                        :mname,
                        :lname,
                        :extension,
                        :email,
                        :pass,
                        :pAdd,
                        :rAdd,
                        :contact,
                        :birthdate,
                        :type,
                        CURDATE(),
                        -- :date_join
                        :serviceToProvide,
                        :typeDetailes
                    )";
                $result = $this->obj->CreateData($query, $bindData);
                if ($result) {
                    if ($this->json->type == 'Rider') {
                        $message = "
                    Your Request to Join ESY Team is Approved By the Admin, your Password: '" . $this->obj->sanitizer($this->json->pass) . "', you can change the provided password as you want, once your First Logging in is Successfull Thank You. 
                    download the app here https://www.mediafire.com/file/skb4bsau6b2r7tz/rider.apk/file
                    ";
                    }
                    if ($this->json->type == 'At Iba Pa') {
                        $message = "
                    Your Request to Join ESY Team is Approved By the Admin, your Password: '" . $this->obj->sanitizer($this->json->pass) . "', you can change the provided password as you want, once your First Logging in is Successfull Thank You. 
                    download the app here https://www.mediafire.com/file/6zll8jgh7jq8axu/atbp.apk/file
                    ";
                    }
                    $this->signal->addId('Provider', "0", (int)$result);
                    mail($this->obj->sanitizer($this->json->email), "Esy Account Verification", $message);
                    return $this->obj->respondSuccess($response, $result);
                }
                if (!$result) return $this->obj->respondFailed($response, $result);
            }
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }
    public function getOneProvider(Request $request, Response $response, $params): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            if ($request->getMethod() === 'POST' || $request->getMethod() === 'GET') {
                $responseGPD = $this->getProviderDetailes($request, $params);
                if ($responseGPD) return $this->obj->respondSuccess($response, $responseGPD);
                if (!$responseGPD) return $this->obj->respondFailed($response, "No Data Found!");
            }
            if ($request->getMethod() === 'PUT' || $request->getMethod() === 'PATCH') {
                $responseUP = $this->updateProvider($request, $params);
                if ($responseUP) return $this->obj->respondSuccess($response, $responseUP);
                if (!$responseUP) return $this->obj->respondFailed($response, "Something Went Wrong!");
            }
            if ($request->getMethod() === 'DELETE') {
                $responseRP = $this->rmProvider($request, $params);
                if ($responseRP) return $this->obj->respondSuccess($response, $responseRP);
                if (!$responseRP) return $this->obj->respondFailed($response, "Successfully Failed!");
            }
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }
    private function rmProvider($request, $params)
    {
        $query = "DELETE from providers where id=:id";
        $where = array(
            ":id" => (int)$params['pId']
        );
        return $this->obj->ExecuteData($query, $where);
    }
    private function updateProvider($request, $params)
    {
        return false;
    }
    private function getProviderDetailes($request, $params)
    {

        $check = "SELECT * FROM providers where id = :id";
        $where = array(
            // ":id"=>$this->json->pId || (int)$params['pId']
            ":id" => (int)$params['pId']
        );
        $checkRes = $this->obj->getSingleData($check, $where);
        if ($checkRes) {
            foreach ($checkRes as $row) {
                $resp = array(
                    "id" => $row->id,
                    "name" => $row->lname . ', ' . $row->fname . ' ' . $row->mname . ' ' . $row->extension,
                    "username" => $row->uname,
                    "email" => $row->email,
                    "stars" => $row->stars,
                    "rewardspoint" => $row->rewardspoint,
                    "dateJoined" => $row->date_join,
                    "type" => $row->type,
                    "serviceToProvide"=>$row->serviceToProvide,
                    "contact" => $row->contact
                );
            }
            return $resp;
        }
        if (!$checkRes) {
            return false;
        }
    }
    //services
    public function addService(Request $request, Response $response): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $check = "SELECT * from services where service_type = :service_type ";
            $where = array(
                ':service_type' => $this->obj->sanitizer($this->json->service_type),
            );
            $checkRes = $this->obj->getSingleData($check, $where);
            if ($checkRes) {
                return $this->obj->respondFailed($response, "Existing");
            }
            if (!$checkRes) {
                $bindData = array(
                    ':service_type' => $this->json->service_type,
                    // ':totalproviders'=>0
                );
                $query = "INSERT into services(service_type) values(:service_type)";
                $result = $this->obj->CreateData($query, $bindData);
                return $this->obj->respondSuccess($response, $result);
            }
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }
    public function getAllServices(Request $request, Response $response): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $query = $this->obj->getData("SELECT * FROM services");
            if (!$query) {
                return $this->obj->respondFailed($response, "No Data Found!");
            }
            if ($query) {
                return $this->obj->respondSuccess($response, $query);
            }
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }
    public function getOneService(Request $request, Response $response, $params): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $reqMethod = $request->getMethod();
            if ($reqMethod == 'delete' || $reqMethod == 'DELETE') {
                $deleteService = $this->deleteService((int)$params['sId']);
                if ($deleteService)  return $this->obj->respondSuccess($response, "DELETED");
                if (!$deleteService) return $this->obj->respondFailed($response, "Failed");
            }
            if ($reqMethod == 'patch' || $reqMethod == 'PATCH') {
                $editService = $this->editService((int)$params['sId'], (string)$this->obj->sanitizer($this->json->updateService));
                if ($editService)  return $this->obj->respondSuccess($response, "Updated");
                if (!$editService) return $this->obj->respondFailed($response, "Failed");
            }
            $query = "SELECT services.id AS id ,services.service_type AS service_type, subservices.id AS subid,subservices.service_type AS subtype  FROM services JOIN subservices ON  subservices.typeid = services.id WHERE typeid = :id ";
            $where = array(
                ":id" => $params['sId']
            );
            $result = $this->obj->getSingleData($query, $where);
            if ($result) {
                foreach ($result as $row) {
                    $subService[] =  array(
                        "subid" => $row->subid,
                        "subtype" => $row->subtype,
                    );
                    $responseArray = array(
                        "type" => $row->service_type,
                        'subServices' => $subService
                    );
                }
                return $this->obj->respondSuccess($response, $responseArray);
            }
            if (!$result) {
                $query = "SELECT id,service_type as type from services where id =:id ";
                $typneOnlyResult = $this->obj->getSingleData($query, $where);
                return $this->obj->respondSuccess($response, $typneOnlyResult[0]);
                // return $this->obj->respondFailed($response,"No Data Found!");
            }
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }
    private function deleteService(int $id)
    {
        $deleteService = "DELETE from services where id = :typeid ";
        $where = array(
            ":typeid" => $id
        );
        $result = $this->obj->ExecuteData($deleteService, $where);
        if ($result) return true;
        if (!$result) return false;
    }
    private function editService(int $serviceid, string $updateString)
    {
        $toUpdateService = "UPDATE services set service_type=:service_type where id=:serviceid ";
        $where = array(
            ":serviceid" => (string)$serviceid,
            ":service_Type" => $updateString,
        );
        $result = $this->obj->ExecuteData($toUpdateService, $where);
        if ($result) return true;
        if (!$result) return false;
    }
    public function addSubService(Request $request, Response $response): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $checkService = "SELECT * FROM  services where service_type = :type ";
            $checkServiceWhere = array(
                ":type" => ucfirst($this->obj->sanitizer($this->json->service_type)),
            );
            $isValid = $this->obj->getSingleData($checkService, $checkServiceWhere);
            if (!empty($isValid)) {
                $check = "SELECT * FROM subservices where service_type = :service_type";
                $where = array(
                    ":service_type" => $this->obj->sanitizer($this->json->service)
                );
                $result = $this->obj->getSingleData($check, $where);
                if (!empty($result)) {
                    return $this->obj->respondFailed($response, "Existing");
                }
                $addService = "INSERT into subservices values(null,:typeid,:service_type)";
                $bindData = array(
                    ":typeid" => $isValid[0]->id,
                    ":service_type" => ucfirst($this->obj->sanitizer($this->json->service))
                );
                $addSubService = $this->obj->CreateData($addService, $bindData);
                if ($addSubService) return $this->obj->respondSuccess($response, array(
                    "typeid" => $isValid[0]->id,
                    "message" => "New Sub-Service Added!"
                ));
                if (!$addSubService) return $this->obj->respondFailed($response, $addSubService);
            }
            return $this->obj->respondFailed($response, "Invalid Service Type!");
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }
    public function deleteSubService(Request $request, Response $response, $params): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $deleteSubService = "DELETE from subservices where id = :subid ";
            $where = array(
                ":subid" => (int)$params['subid']
            );
            $result = $this->obj->ExecuteData($deleteSubService, $where);
            if ($result) return $this->obj->respondSuccess($response, "Deleted");
            if (!$result) return $this->obj->respondFailed($response, $result);
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }

    //customer
    public function getAllCustomer(Request $request, Response $response): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $query = $this->obj->getData("SELECT * From users where isAdmin ='Customer' ");
            if (!$query) {
                return $this->obj->respondFailed($response, "No Data Found!");
            }
            if ($query) {
                foreach ($query as $row) {
                    $resp[] = array(
                        "id" => $row->id,
                        "name" => $row->lname . ', ' . $row->fname . ', ' . $row->mname,
                        // "username"=>$row->uname,
                        "email" => $row->email,
                        "dateJoined" => $row->date_join,
                        // "type"=>$row->type
                        // "isVerified"=>$row->verified,
                        "prof" => '',
                    );
                }
                return $this->obj->respondSuccess($response, $resp);
            }
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }

    public function getOneCustomer(Request $request, Response $response, $params): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            if ($request->getMethod() === 'POST' || $request->getMethod() === 'GET') {
                $responseGPD = $this->getCustomerDetailes($request, $params);
                if ($responseGPD) return $this->obj->respondSuccess($response, $responseGPD);
                if (!$responseGPD) return $this->obj->respondFailed($response, "No Data Found!");
            }
            if ($request->getMethod() === 'PUT' || $request->getMethod() === 'PATCH') {
                $responseUP = $this->updateProvider($request, $params);
                if ($responseUP) return $this->obj->respondSuccess($response, $responseUP);
                if (!$responseUP) return $this->obj->respondFailed($response, "Something Went Wrong!");
            }
            if ($request->getMethod() === 'DELETE') {
                $responseRP = $this->rmProvider($request, $params);
                if ($responseRP) return $this->obj->respondSuccess($response, $responseRP);
                if (!$responseRP) return $this->obj->respondFailed($response, "Successfully Failed!");
            }
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }

    private function getCustomerDetailes($request, $params)
    {

        $check = "SELECT * FROM users where id = :id";
        $where = array(
            // ":id"=>$this->json->pId || (int)$params['pId']
            ":id" => (int)$params['cliId']
        );
        $checkRes = $this->obj->getSingleData($check, $where);
        if ($checkRes) {
            foreach ($checkRes as $row) {
                $resp = array(
                    "id" => $row->id,
                    "name" => $row->lname . ', ' . $row->fname . ' ' . $row->mname . ' ' . $row->extension,
                    // "username"=>$row->uname,
                    "email" => $row->email,
                    "dateJoined" => $row->date_join,
                    // "isVerified"=>$row->verified,
                    "contact" => $row->contact
                );
            }
            return $resp;
        }
        if (!$checkRes) {
            return false;
        }
    }

    //rewards
    public function addReward(Request $request, Response $response): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $item = $this->obj->sanitizer($this->json->item);
            $desc = $this->obj->sanitizer($this->json->desc);
            $points = $this->obj->sanitizer($this->json->points);
            $stock = $this->obj->sanitizer($this->json->stock);
            try {
                $checkReward = $this->obj->getSingleData("SELECT item FROM rewards where item = :reward ", [":reward" => $item]);
                if ($checkReward) return $this->obj->respondSuccess($response, "Already Exists");
                if (!$checkReward) {
                    $queryReward = "INSERT INTO rewards values(null,:item,:desc,:points,:stock)";
                    $bindData = array(
                        ":item" => $item,
                        ":desc" => $desc,
                        ":points" => $points,
                        ":stock" => $stock,
                    );
                    $result = $this->obj->CreateData($queryReward, $bindData);
                    if ($result) return $this->obj->respondSuccess($response, "Reward Added");
                    if (!$result) return $this->obj->respondFailed($response, "Something Went Wrong!");
                }
            } catch (\Throwable $e) {
                return $this->obj->respondFailed($response, $e);
            }
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }
    public function getRewards(Request $request, Response $response): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $query = $this->obj->getData("SELECT * From rewards");
            if (!$query) {
                return $this->obj->respondFailed($response, "No Data Found!");
            }
            return $this->obj->respondSuccess($response, $query);
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }
    public function UpdateReward(Request $request, Response $response, $params): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $id = $this->obj->sanitizer($this->json->id);
            $item = $this->obj->sanitizer($this->json->item);
            $desc = $this->obj->sanitizer($this->json->desc);
            $points = $this->obj->sanitizer($this->json->points);
            $stock = $this->obj->sanitizer($this->json->stock);
            try {
                if ($id !== $params['rewardId']) return $this->obj->respondFailed($response, "Invalid Request");
                $queryReward = "UPDATE rewards set item = :item,description = :desc,points = :points, stock =:stock where id = :rewardId ";
                $bindData = array(
                    ":rewardId" => $id,
                    ":item" => $item,
                    ":desc" => $desc,
                    ":points" => (int)$points,
                    ":stock" => $stock,
                );
                $result = $this->obj->ExecuteData($queryReward, $bindData);
                if ($result) return $this->obj->respondSuccess($response, "Reward Updated");
                if (!$result) return $this->obj->respondFailed($response, "Something Went Wrong!");
            } catch (\Throwable $e) {
                return $this->obj->respondFailed($response, $e);
            }
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }

    //claiming
    public function getClaimRequests(Request $request, Response $response): Response
    {
        $tokenCh = $this->checkToken($request);
        if ($tokenCh) {
            $queryRequests = "";
            if ($this->obj->sanitizer($this->json->type) === 'Customer') { // if type true customer
                $queryRequests = $this->obj->getData("SELECT claimrewards_c.id as id,fname,lname,mname,email,item FROM claimrewards_c JOIN users JOIN rewards ON claimerId = users.id && rewardId = rewards.id WHERE isApproved = 0");
            }
            if ($this->obj->sanitizer($this->json->type) === 'Provider') { // if type false not customer
                $queryRequests = $this->obj->getData("SELECT claimrewards_p.id as id,fname,lname,mname,email,item FROM claimrewards_p JOIN providers JOIN rewards ON claimerId = providers.id && rewardId = rewards.id WHERE isApproved = 0");
            }
            if ($queryRequests) return $this->obj->respondSuccess($response, $queryRequests);
            if (!$queryRequests) return $this->obj->respondFailed($response, 'No Data Found');
        }
        if (!$tokenCh) return $this->noUserFound($response);
    }
}
