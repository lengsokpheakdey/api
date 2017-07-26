<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/api/users/register', function (Request $request, Response $response) {

    $obj = new helperClass();

    $firstname = $request->getParam('firstname');
    $lastname = $request->getParam('lastname');
    $username = $request->getParam('username');
    $userpassword = $request->getParam('userpassword');
    $email = $request->getParam('email');
    $phone = $request->getParam('phone');

    $require_feild = array("username" => $username,
                            "userpassword" => $userpassword,
                            "email" => $email);

    try {
        if ($obj->validations($require_feild) == false) {
            $obj->json_response(0,"message",$require_feild);
            return;
        }
        //check username
        if($obj->isUserExist("username",$username)){
            echo $obj->json_response(0, "message", "Username already taken!");
            return;
        }
        //check email
        if($obj->isUserExist("email",$email)){
            echo $obj->json_response(0, "message", "Email already taken!");
            return;
        }

        //check phonenumber
        if($obj->isUserExist("phone",$phone)){
            echo $obj->json_response(0, "message", "Phone number already taken!");
            return;
        }

        $gender = $request->getParam('gender');
        $api_token = $obj->generateToken($email,$userpassword);
        $userpassword = $obj->passwordEncryption($userpassword);
        $address = $request->getParam('address');
        $job = $request->getParam('job');

        $sql = "INSERT INTO tblusers (
                                firstname,
                                 lastname,
                                  gender,
                                   username,
                                    userpassword,
                                     email,
                                      api_token,
                                       phone,
                                        address,
                                         job
                                         ) 
                VALUE (:firstname, :lastname, :gender, :username, :userpassword, :email, :api_token, :phone, :address, :job)";
        $conn = $obj->connection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);
        $stmt->bindParam(':lastname', $lastname, PDO::PARAM_STR);
        $stmt->bindParam(':gender', $gender, PDO::PARAM_STR);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':userpassword', $userpassword, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':api_token', $api_token, PDO::PARAM_STR);
        $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindParam(':address', $address, PDO::PARAM_STR);
        $stmt->bindParam(':job', $job, PDO::PARAM_STR);
        $stmt->execute();
        $user_inserted = helperClass::executeSql("SELECT * FROM `v_getusers` WHERE email='".$email."'");
        echo $obj->json_response(1, "response", $user_inserted);
        $stmt = null;
        $obj = null;
        return;

    } catch (PDOException $exception) {
        echo $exception;
    }
});

$app->post('/api/users/login', function (Request $request, Response $response) {
    try{
        $obj = new helperClass();
        //$email = $request->getParam('email');
        $password = $request->getParam('password');
        $username = $request->getParam('username');
        //$phone = $request->getParam('phone');

        //login by email
        if ((isset($username)) && !(empty($username)) && (isset($password)) && !(empty($password))){
            $password = $obj->passwordEncryption($password);
            $sql ="SELECT COUNT(*) AS usercount, userid as id FROM tblusers WHERE username = :username OR email = :username OR phone = :username AND userpassword = :password GROUP BY userid";
            $conn = $obj->connection();
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_EVT_FREE);
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                if ($row['id']) {
                    echo $obj->getUserBy($row['id']);
                    $obj = null;
                    $stmt = null;
                    return;
                }
            }
        }
//
//        //login by username
//        if((isset($username)) && !(empty($username)) && (isset($password)) && !(empty($password))){
//            if($obj->isUserExist("username",$username)){
//                $password = $obj->passwordEncryption($password);
//                $sql ="SELECT userid AS id FROM tblusers WHERE  username=:username and userpass=:password";
//                $conn = $obj->connection();
//                $stmt = $conn->prepare($sql);
//                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
//                $stmt->bindParam(':password', $password, PDO::PARAM_STR);
//                $stmt->execute();
//                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
//                    if ($row['id']) {
//                        echo $obj->getUserBy($row['id']);
//                        $obj = null;
//                        $stmt = null;
//                        return;
//                    }
//                }
//            }
//        }
//
//        //login by phone
//        if((isset($phone)) && !(empty($phone)) && (isset($password)) && !(empty($password))){
//            if($obj->isUserExist("phone",$phone)){
//                $password = $obj->passwordEncryption($password);
//                $sql ="SELECT userid AS id FROM tblusers WHERE  phone=:phone and password=:password";
//                $conn = $obj->connection();
//                $stmt = $conn->prepare($sql);
//                $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
//                $stmt->bindParam(':password', $password, PDO::PARAM_STR);
//                $stmt->execute();
//                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
//                    if ($row['id']) {
//                        echo $obj->getUserBy($row['id']);
//                        $obj = null;
//                        $stmt = null;
//                        return;
//                    }
//                }
//            }
//        }

        echo $obj->json_response(0, "message", "Invalid users.");
        $obj = null;
        $stmt = null;
    }
    catch (PDOException $exception){
        echo $exception;
    }
});

$app->get('/api/users/load/{userid}', function (Request $request, Response $response) {
    $obj = new helperClass();
    $id=$request->getAttribute("userid");
    if(!$obj->isUserExist("userid",$id)){
        echo $obj->json_response(0, "message", "Invalid users!");
        return;
    }
    $result = $obj->  userValidation($request);
    if ($result['id']) {
        echo $obj->getUserBy($result['id']);
        $obj = null;
        $stmt = null;
        return;
    }
});

$app->post('/api/users/editprofile/{userid}', function (Request $request, Response $response) {
    $obj = new helperClass();
    $id=$request->getAttribute("userid");
    if(!$obj->isUserExist("userid",$id)){
        echo $obj->json_response(0, "message", "Invalid users!");
        return;
    }
    $result = $obj-> userValidation($request);
    if ($result['id']) {
        //Get data from users post
        $firstname = $request->getParam('firstname');
        $lastname = $request->getParam('lastname');
        $phone = $request->getParam('phone');
        $gender = $request->getParam('gender');
        $address = $request->getParam('address');
        $job = $request->getParam('job');
        $photo = $request->getUploadedFiles();
        $username = $request->getParam('username');

        //check username
        if(isset($username)){
            if($obj->isUserExist("username",$username)){
                echo $obj->json_response(0, "message", "Username already taken!");
                return;
            }
        }

        //Delete old photo
        if(!(empty($photo['photo'])) && isset($photo['photo'])){
            $name = $result['photo'];
            $path = $_SERVER['DOCUMENT_ROOT'].'/images/'.$name;
            if(!(empty($name))) {
                if (file_exists($path)) {
                    if (!unlink($path)) {
                        //                    echo $path;
                        //                    echo ("Error deleting" . $path);
                    } else {
                        //                    echo ("Deleted" . $path);
                    }
                }
            }
        }

        //Check if users don't provide data
        $firstname = empty($firstname) || ctype_space($firstname) ? $result['firstname'] : $firstname;
        $lastname = empty($lastname) || ctype_space($lastname) ? $result['lastname'] : $lastname;
        $phone = empty($phone) || ctype_space($phone) ? $result['phone'] : $phone;
        $gender = empty($gender) || ctype_space($gender) ? $result['gender'] : $gender;
        $address = empty($address) || ctype_space($address) ? $result['address'] : $address;
        $job = empty($job) || ctype_space($job) ? $result['job'] : $job;
        $photo = empty($photo['photo']) || ctype_space($photo['photo']) || !(isset($photo['photo'])) ? $result['photo'] : $obj->uploadImage();
        $username = empty($username) || ctype_space($username) ? $result['username'] : $username;

        $user    = $result['userid'];
        $token   = $result['api_token'];

        $sql = "UPDATE tblusers SET firstname = :firstname,
                                    lastname = :lastname,
                                    gender = :gender,
                                    phone = :phone,
                                    address = :address,
                                    job= :job,
                                    photo = :photo,
                                    username = :username
        WHERE userid=:id AND api_token=:api";

        $conn = $obj->connection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':firstname', $firstname, PDO::PARAM_STR);
        $stmt->bindParam(':lastname', $lastname, PDO::PARAM_STR);
        $stmt->bindParam(':gender', $gender, PDO::PARAM_STR);
        $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindParam(':address', $address, PDO::PARAM_STR);
        $stmt->bindParam(':job', $job, PDO::PARAM_STR);
        $stmt->bindParam(':id', $user, PDO::PARAM_STR);
        $stmt->bindParam(':api', $token, PDO::PARAM_LOB);
        $stmt->bindParam(':photo', $photo, PDO::PARAM_LOB);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);

        if ($stmt->execute()){
            echo $obj->getUserBy($user);
            $obj = null;
            $stmt = null;
        }
        return;
    }
});

$app->post('/api/users/security/{userid}', function (Request $request, Response $response) {

    $obj = new helperClass();
    $id=$request->getAttribute("userid");
    if(!$obj->isUserExist("userid",$id)){
        echo $obj->json_response(0, "message", "Invalid users!");
        return;
    }
    $result = $obj-> userValidation($request);
    if ($result['userid']) {
        $userpassword = $request->getParam('userpassword');

        $currentPassword = $request->getParam('currentPassword');
        $newPassword = $request->getParam('newPassword');
        $confirmPassword = $request->getParam('confirmPassword');


        //Check if users don't provide data
        $userpassword = empty($userpassword) || ctype_space($userpassword) ? $result['userpassword'] :$obj->passwordEncryption($userpassword);
        $currentPassword = empty($currentPassword) || ctype_space($currentPassword) ? "" : $obj->passwordEncryption($currentPassword);
        $newPassword = empty($newPassword) || ctype_space($newPassword) ? "" : $obj->passwordEncryption($newPassword);
        $confirmPassword = empty($confirmPassword) || ctype_space($confirmPassword) ? "" : $obj->passwordEncryption($confirmPassword);

        $user    = $result['userid'];
        $token   = $result['api_token'];

        if(!empty($currentPassword) && !empty($newPassword) && !empty($confirmPassword)){
            if(!$obj->passwordCompare($userpassword,$currentPassword)){
                echo $obj->json_response(0, "message", "Current Password not match!");
                return;
            }
            if(!$obj->passwordCompare($newPassword, $confirmPassword)){
                echo $obj->json_response(0, "message", "Confirm Password not match!");
                return;
            }

            $sql = "UPDATE tblusers SET userpassword = :userpassword
                    WHERE userid=:id AND api_token=:api";
            $conn = $obj->connection();
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':userpassword', $confirmPassword, PDO::PARAM_STR);
            $stmt->bindParam(':id', $user, PDO::PARAM_STR);
            $stmt->bindParam(':api', $token, PDO::PARAM_STR);
            if ($stmt->execute()){
                echo $obj->getUserBy($user);
                $obj = null;
                $stmt = null;
            }
            return;
        }
        echo $obj->json_response(0, "message", "No change occur!.");
        return;
    }
});

$app->post('/api/users/sociallogin', function (Request $request, Response $response) {
    $obj = new helperClass();
    $facebook_id = $request->getParam('facebook_id');
    $google_id = $request->getParam('google_id');

    if( (empty($facebook_id) || !(isset($facebook_id))) && (empty($google_id) || !(isset($google_id)))  )
    {
        echo $obj->json_response("0",'message',"social id required!");
        return;
    }

    $param = isset($facebook_id) ? $facebook_id : $google_id;
    $str = isset($facebook_id) ? "facebook_id" : "google_id";
    $sql = "SELECT count(*) as usercount FROM `v_getusers` WHERE ". $str . "=:id";
    $conn = $obj->connection();
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $param, PDO::PARAM_STR);
    $stmt->execute();
    while ($result = $stmt->fetch(PDO::FETCH_ASSOC)){
        if ($result['usercount']){
            $user_inserted = helperClass::executeSql("SELECT * FROM `v_getusers` WHERE ".$str."='".$param."'");
            echo $obj->json_response(1, "response", $user_inserted);
            $stmt = null;
            $obj = null;
            return;
        }
    }
    $fname = $request->getParam('fname');
    $lname =$request->getParam('lname');
    $gender = $request->getParam('gender');
    $email = $request->getParam('email');
    $api_token = $obj->generateToken($param);
    $photo = $request->getParam('photo');

    $sql = "INSERT INTO tblusers (firstname,
                                     lastname,
                                      gender,
                                         email,
                                            api_token,
                                              photo,
                                                $str) 
                    VALUE (:firstname, :lastname, :gender, :email, :api_token, :photo, :social)";
    $conn = $obj->connection();
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':firstname', $fname, PDO::PARAM_STR);
    $stmt->bindParam(':lastname', $lname, PDO::PARAM_STR);
    $stmt->bindParam(':gender', $gender, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':api_token', $api_token, PDO::PARAM_STR);
    $stmt->bindParam(':photo', $photo, PDO::PARAM_STR);
    $stmt->bindParam(':social', $param, PDO::PARAM_STR);
    $stmt->execute();
    $user_inserted = helperClass::executeSql("SELECT * FROM `v_getusers` WHERE ".$str."='".$param."'");
    echo $obj->json_response(1, "response", $user_inserted);
    $stmt = null;
    $obj = null;
    return;
});

$app->get('/api/users/all', function (Request $request, Response $response) {
    $obj= new helperClass();
    $sql ="SELECT * FROM `v_getusers`";
    $conn = $obj->connection();
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        if ($row['userid']) {
            $user_inserted = helperClass::executeSql("SELECT * FROM `v_getusers`");
            return $obj -> json_response(1, "response", $user_inserted);
            $obj = null;
            $stmt = null;
        }
    }
});




