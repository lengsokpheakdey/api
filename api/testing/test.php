<?php
//
//use \Psr\Http\Message\ServerRequestInterface as Request;
//use \Psr\Http\Message\ResponseInterface as Response;
//
//$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);
//
//
//$app->get('/api/testings', function (Request $request, Response $response) {
//    require '../config/db.php';
//    $sql = "SELECT * FROM tblusers";
//    $result = $mysqli->query($sql);
//    while  ($row = $result->fetch_assoc()){
//        $data [] = $row;
//    }
//
//    echo json_encode($data);
//
//});
//
//
//
//
//$app->get('/api/echo', function (Request $request, Response $response) {
//    $data = array("red","greed","blue");
//    array_push($data,"haha",$data);
//    echo json_encode($data);
//});

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);

//Get all users
$app->get('/api/users', function (Request $request, Response $response) {
    $sql = "SELECT * FROM tblusers";
    try{
        $db = new databaseconnection();
        $db = $db->pdoconnection();

        $stmt = $db->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        echo  json_encode($result);
    }
    catch (PDOException $exception){

    }
});


//Get single users
$app->get('/api/users/{id}', function (Request $request, Response $response) {
    header('Content-Type: application/json');
    $id = $request->getAttribute('id');

    $sql = "SELECT * FROM tblusers WHERE userid=$id";
    try{
        $db = new databaseconnection();
        $db = $db->pdoconnection();

        $stmt = $db->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo  json_encode($result);
    }
    catch (PDOException $exception){

    }
});


$app->get('/api/userssqli', function (Request $request, Response $response) {
    require '../config/db.php';
    $sql = "SELECT * FROM tblusers";
    $result = $mysqli->query($sql);
    while  ($row = $result->fetch_assoc()){
        $data [] = $row;
    }

    echo json_encode($data);
});

$app->get('/api/returnA', function (Request $request, Response $response) {
    echo phpversion1();
});



//add users /{id}
$app->post('/api/users/adds', function (Request $request, Response $response) {
    $userid = $request->getParam('userid');
    $firstname = $request->getParam('firstname');
    $lastname = $request->getParam('lastname');
    $gender  = $request->getParam('gender');
    $username = $request->getParam('username');
    $userpassword = $request->getParam('userpassword');
    $email = $request->getParam('email');
    $phone = $request->getParam('phone');
    $address = $request->getParam('address');
    $job = $request->getParam('job');

//    $param = array();
//    $param[] = $userid;
//    $param[] = $firstname;
//    array_push($param,array("name=>",$firstname . ' ' .$lastname));
    //validation()

    $sql ="INSERT INTO tblUsers (userid, firstname, lastname, gender, username, 
          userpassword,email, phone, address, job) 
          VALUE (:userid, :firstname, :lastname, :gender, :username,
          :userpassword, :email, :phone, :address, :job)";

    try{
        $db = new databaseconnection();
        $db = $db->pdoconnection();
        $stmt = $db->prepare($sql);

//        $stmt->bindParam($userid,$firstname,$lastname,$gender,$username,$userpassword,$email,$phone,$address,$job);

        $stmt->bindParam(':userid',$userid,PDO::PARAM_INT);
        $stmt->bindParam(':firstname',$firstname,PDO::PARAM_STR);
        $stmt->bindParam(':lastname',$lastname,PDO::PARAM_STR);
        $stmt->bindParam(':gender',$gender,PDO::PARAM_STR);
        $stmt->bindParam(':username',$username,PDO::PARAM_STR);
        $stmt->bindParam(':userpassword',$userpassword,PDO::PARAM_STR);
        $stmt->bindParam(':email',$email,PDO::PARAM_STR);
        $stmt->bindParam(':phone',$phone,PDO::PARAM_STR);
        $stmt->bindParam(':address',$address,PDO::PARAM_STR);
        $stmt->bindParam(':job',$job,PDO::PARAM_STR);
        $stmt->execute();
        echo"Inserted!";
    }
    catch(PDOException $exception){
        echo $exception;
    }

//    $sql = "SELECT * FROM tblusers WHERE userid=$id";
//    try{
//        $db = new databaseconnection();
//        $db = $db->pdoconnection();
//
//        $stmt = $db->query($sql);
//        $result = $stmt->fetchAll(PDO::FETCH_OBJ);
//        echo  json_encode($result);
//    }
//    catch (PDOException $exception){
//
//    }
    //echo json_encode(array("code" => "402", "data" => $param));
});


function validation($param){
    echo $param;
}


$app->get('/api/test/a', function (Request $request, Response $response) {
    echo "hello a";
});






//GET CHAPTERS
$app->get(
    '/api/tblusers',
    function () {
        executeSql('SELECT * FROM tblusers ORDER BY userid DESC');
    }
);


////GENERIC SQL EXECUTE
//function executeSql($sql) use ($app) {
//    $app = \Slim\App::getInstance();
//    $app->contentType('application/json');
//    try {
//        $db = getConnection();
//        $stmt = $db->query($sql);
//        $results = $stmt->fetchAll(PDO::FETCH_OBJ);
//        $db = null;
//        echo json_encode($results);
//    } catch(PDOException $e) {
//        echo '{"error":{"text":'. $e->getMessage() .'}}';
//    }
//}