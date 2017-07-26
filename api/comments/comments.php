<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


$app->post('/api/comments/addcomment/{userid}', function (Request $request, Response $response) {
    $obj = new helperClass();
    $id=$request->getAttribute("userid");
    if(!$obj->isUserExist("userid",$id)){
        echo $obj->json_response(0, "message", "Invalid users!");
        return;
    }
    $result = $obj-> userValidation($request);
    if ($result['id']) {
        $postid = $request->getParam('postid');
        $userid = $result['id'];
        $description = $request->getParam('description');
        $commentdate = date("Y-m-d");

        if (!$obj->isPostExist("postid", $postid)) {
            echo $obj->json_response(0, "message", "Invalid post!");
            return;
        }

        $require_feild = array("description" => $description);

        if ($obj->validations($require_feild) == false) {
            $obj->json_response(0,"message", $require_feild);
            return;
        }

        $sql = "INSERT INTO tblcomments(
                                userid,
                                postid,
                                description,
                                commentdate) 
                VALUE (:userid,:postid, :description, :commentdate)";

        $conn = $obj->connection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':postid', $postid, PDO::PARAM_STR);
        $stmt->bindParam(':commentdate', $commentdate, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);

        if ($stmt->execute()){
            echo $obj->json_response(1, "response", "Comment post successfully!");
            $obj = null;
            $stmt = null;
        }
        return;
    }
});

$app->get('/api/comments/show/', function (Request $request, Response $response) {
    echo date("Y-m-d H:i:s");
    return;
});

$app->post('/api/comments/upload/', function (Request $request, Response $response) {
    $obj = new MultiUpload();
    $obj->uploadImage();
});
