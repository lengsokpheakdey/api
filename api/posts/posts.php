<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/api/posts/addpost/{userid}', function (Request $request, Response $response) {
    $obj = new helperClass();
    $id=$request->getAttribute("userid");
    if(!$obj->isUserExist("userid",$id)){
        echo $obj->json_response(0, "message", "Invalid users!");
        return;
    }
    $result = $obj-> userValidation($request);
    if ($result['id']) {
        $lat = $request->getParam('lat');
        $long = $request->getParam('long');
        $title = $request->getParam('title');
        $desc = $request->getParam('desc');
        $photo = $request->getUploadedFiles();
        $condition = $request->getParam('condition');
        $expiredate = $request->getParam('expiretime');

        $require_feild = array("description" => $desc,
            "condition" => $condition,
            "expiretime" => $expiredate,
            "title"=>$title);

        if ($obj->validations($require_feild) == false) {
            $obj->json_response(0,"message", $require_feild);
            return;
        }

        $photo = empty($photo['photo']) || ctype_space($photo['photo']) ? "" : $obj->uploadImage();


        $entrydate=date("Y-m-d H:i:s");
        $expiredate = date_create($expiredate);
        $expiredate = date_format($expiredate,"Y-m-d H:i:s");

        $sql = "INSERT INTO tblposts (
                                userid,
                                title,
                                 entrydate,
                                  lat,
                                    lon,
                                    description,
                                     image,
                                     expiredate,
                                     status) 
                VALUE (:userid,:title, :entrydate, :lat, :lon, :description, :photo, :expiredate, :condition)";

        $conn = $obj->connection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $id, PDO::PARAM_STR);
        $stmt->bindParam(':entrydate', $entrydate, PDO::PARAM_STR);
        $stmt->bindParam(':lat', $lat, PDO::PARAM_STR);
        $stmt->bindParam(':lon', $long, PDO::PARAM_STR);
        $stmt->bindParam(':description', $desc, PDO::PARAM_STR);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':photo', $photo, PDO::PARAM_STR);
        $stmt->bindParam(':expiredate', $expiredate, PDO::PARAM_STR);
        $stmt->bindParam(':condition', $condition, PDO::PARAM_STR);

        if ($stmt->execute()){
            echo $obj->json_response(1, "response", "Insert post successfully!");
            $obj = null;
            $stmt = null;
        }
        return;
    }
});

$app->post('/api/posts/editpost/{userid}', function (Request $request, Response $response) {
    $obj = new helperClass();
    $userid=$request->getAttribute("userid");
    if(!$obj->isUserExist("userid",$userid)){
        echo $obj->json_response(0, "message", "Invalid users!");
        return;
    }
    $result = $obj-> userValidation($request);
    if ($result['id']) {
        $postid = $request->getParam('postid');

        if (!$obj->isPostExist("postid", $postid)) {
            echo $obj->json_response(0, "message", "Invalid post!");
            return;
        }

        if (!$obj->isPostOfUser($postid, $userid)) {
            echo $obj->json_response(0, "message", "Sorry you can't edit other users post!");
            return;
        }

        $post = $obj->getPost($postid);

        //get data from users post
        $entrydate = $request->getParam('entrydate');
        $lat = $request->getParam('lat');
        $long = $request->getParam('long');
        $desc = $request->getParam('desc');
        $title = $request->getParam('title');
        $photo = $request->getUploadedFiles();
        $con = $request->getParam('con');
        $expiredate = $request->getParam('expiretime');

        $imgName = str_replace("http://212.237.29.120/images/","",$post['image']);
        //Delete old photo
        if (!(empty($photo['photo'])) && isset($photo['photo'])) {
//            $name =  $post['image'];
//            $name = str_replace("trafficjam/images/","",$post['image']);
            $path = $_SERVER['DOCUMENT_ROOT'] . '/images/' . $imgName;
            if (!(empty($imgName))) {
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
        $entrydate = empty($entrydate) || ctype_space($entrydate) ? $post['postdate'] : $entrydate;
        $lat = empty($lat) || ctype_space($lat) || !(isset($lat)) ? $post['latitute'] : $lat;
        $long = empty($long) || ctype_space($long) || !(isset($long)) ? $post['longtitute'] : $long;
        $desc = empty($desc) || ctype_space($desc) || !(isset($desc)) ? $post['description'] : $desc;
        $title = empty($title) || ctype_space($title) || !(isset($title)) ? $post['title'] : $title;
        $con = empty($con) || ctype_space($con) || !(isset($con)) ? $post['status'] : $con;
        $expiredate = empty($expiredate) || ctype_space($expiredate) || !(isset($expiredate)) ? $post['expiredate'] : $expiredate;

        $photo = empty($photo['photo']) || ctype_space($photo['photo']) || !(isset($photo['photo'])) ? $imgName : $obj->uploadImage();

        $sql = "UPDATE tblposts SET entrydate = :entrydate,
                                    lat = :lat,
                                    lon = :lon,
                                    description = :description,
                                    image = :photo,
                                    expiredate = :expiredate,
                                    status = :con, 
                                    title= :title
                                    WHERE postid = :postid AND userid = :userid";
        $conn = $obj->connection();
        $stmt = $conn->prepare($sql);
        $entrydate=date_create($entrydate);
        $expiredate=date_create($expiredate);
        $entrydate = date_format($entrydate,"Y-m-d H:i:s");
        $expiredate = date_format($expiredate,"Y-m-d H:i:s");

        $stmt->bindParam(':entrydate', $entrydate, PDO::PARAM_STR);
        $stmt->bindParam(':lat', $lat, PDO::PARAM_STR);
        $stmt->bindParam(':lon', $long, PDO::PARAM_STR);
        $stmt->bindParam(':description', $desc, PDO::PARAM_STR);
        $stmt->bindParam(':photo', $photo, PDO::PARAM_STR);
        $stmt->bindParam(':expiredate', $expiredate, PDO::PARAM_STR);
        $stmt->bindParam(':postid', $postid, PDO::PARAM_STR);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':con', $con, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo $obj->json_response(1, "response", "Update post successfully!");
            $obj = null;
            $stmt = null;
        }
        return;
    }
});

$app->post('/api/posts/getpost/{userid}', function (Request $request, Response $response) {
    $obj = new helperClass();
    $userid=$request->getAttribute("userid");
    if(!$obj->isUserExist("userid",$userid)){
        echo $obj->json_response(0, "message", "Invalid users!");
        return;
    }
    $result = $obj-> userValidation($request);
    if ($result['id']) {
        $postid = $request->getParam('postid');
        if(!$obj->isPostExist("postid",$postid)){
            echo $obj->json_response(0, "message", "Invalid post!");
            return;
        }

        //get post
        $comments = array();
        $sql = "SELECT * FROM  `v_getpostdetail` WHERE postid = $postid and isDelete= 0";
        $conn = $obj->connection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        while ($post = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sql = "SELECT * FROM  `v_getusercomment` WHERE postid = $postid";
            $conn = $obj->connection();
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            while ($comment = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($comments,$comment);
            };
            return $obj->json_response_post(1, $post, $comments);
        };
    }
});

$app->post('/api/posts/deletpost/{userid}', function (Request $request, Response $response) {
    $obj = new helperClass();
    $userid=$request->getAttribute("userid");
    if(!$obj->isUserExist("userid",$userid)){
        echo $obj->json_response(0, "message", "Invalid users!");
        return;
    }
    $result = $obj-> userValidation($request);
    if ($result['id']) {
        $postid = $request->getParam('postid');

        if (!$obj->isPostExist("postid", $postid)) {
            echo $obj->json_response(0, "message", "Invalid post!");
            return;
        }

        if (!$obj->isPostOfUser($postid, $userid)) {
            echo $obj->json_response(0, "message", "Sorry you can't delete other users post!");
            return;
        }

        $sql = "UPDATE tblposts SET isDelete = 1 WHERE postid = :postid AND userid = :userid";
        $conn = $obj->connection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':postid', $postid, PDO::PARAM_STR);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        if ($stmt->execute()) {
            echo $obj->json_response(1, "response", "Delete post successfully!");
            $obj = null;
            $stmt = null;
        }
        return;
    }
});

$app->get('/api/posts/all/{userid}', function (Request $request, Response $response) {
    $obj = new helperClass();
    $id=$request->getAttribute("userid");
    if(!$obj->isUserExist("userid",$id)){
        echo $obj->json_response(0, "message", "Invalid users!");
        return;
    }
    $result = $obj-> userValidation($request);
    if ($result['id']) {
        echo $obj->getPostBy("");
        $obj = null;
        $stmt = null;
        return;
    }
});
