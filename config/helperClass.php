<?php
    use \Psr\Http\Message\ServerRequestInterface as Request;

class helperClass
{
    private $dbhost = 'localhost';
    private $dbuser = 'root';
    private $dbpass = 'Pa$$w0rd';
    private $dbname = 'trafficjamdb';

    //GET CONNECTION
    public function connection()
    {
      //return $link = mysqli_connect($this->dbhost, $this ->dbuser, $this->dbpass, $this->dbname);
        $mysql_connect_str = "mysql:host=$this->dbhost;dbname=$this->dbname";
        $dbConnection = new PDO($mysql_connect_str, $this->dbuser, $this->dbpass);
        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $dbConnection;
    }

    //GENERIC SQL EXECUTE
    public static function executeSql($sql) {
        $db = new helperClass();
        $db = $db->connection();
        $stmt = $db->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $db = null;
        return $results;
        //echo json_encode($results);
    }

    //Get Posts by postid
    public function getPost($postid){
        $sql = "SELECT * FROM  `v_getpostdetail` WHERE postid = $postid and isDelete=0";
        $conn = $this->connection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)){
            if($result['postid']){
                return $result;
            }
        };
    }
    //Get Posts
    public function getPostBy($userid){
        $sql = $userid == "" ? "SELECT * FROM `v_getpostdetail` WHERE isDelete=0" : "SELECT * FROM `v_getpostdetail` WHERE userid ='".$userid."'";
        $userpost = helperClass::executeSql($sql);
        if(isset($token) && !empty($token)){
            return $userpost;
        }
        return $this -> json_response(1, "response",$userpost);
    }

    public function getUserBy($id){
        $user_inserted = helperClass::executeSql("SELECT * FROM `v_getusers` WHERE userid ='".$id."'");
        if(isset($token) && !empty($token)){
            return $user_inserted;
        }
        return $this -> json_response(1, "response", $user_inserted);
    }

    public function emailCheck($email){
        $user_inserted = helperClass::executeSql("SELECT count(*) FROM tblusers WHERE email ='".$email."'");
        if(isset($email) && !empty($email)){
            return $user_inserted;
        }
        return $this -> json_response(1, "response", $user_inserted);
    }

    //Validation input
    public function validations($input)
    {
        foreach($input as $key => $value){
            if(!isset($input[$key]) || empty($input[$key])){
                $msg =" is required";
                $status = 0;
                //echo $key.$msg;
                $obj = new helperClass();
                echo $obj->json_response($status,'message',$key.$msg);
                return false;
                break;
            }

            //Password strength checking
//            if ($input[$key] == $input['userpassword'])
//            {
//                if (!preg_match('/[A-Z]+[a-z]+[0-9]+[^a-zA-Z]+/',$input['userpassword'])){
//                    $msg =" not secure enough";
//                    $status = 0;
//                    $obj = new helperClass();
//                    echo $obj->json_response($status,'message',$key.$msg);
//                    return false;
//                    break;
//                }
//            }
        }
        // All data valid
        return true;
    }

    //Json Response
    public function json_response($code, $key = "response" ,$message = null)
    {
        // clear the old headers
        header_remove();
        // set the actual code
        http_response_code($code);
        // set the header to make sure cache is forced
        header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");
        // treat this as json
        header('Content-Type: application/json');
        $status = array(
            200 => '200 OK',
            400 => '400 Bad Request',
            403 => 'Feild Required',
            422 => 'Unprocessable Entity',
            500 => '500 Internal Server Error'
        );
        // ok, validation error, or failure
        // echo header('Status: '.$status[$code]);
        // return the encoded json
        return json_encode(array(
            'status' => $code, // < 300, // success or not?
            $key => $message
        ));
    }

    //Respond json for post
    public function json_response_post($code,$message = null, $comment = null)
    {
        // clear the old headers
        header_remove();
        // set the actual code
        http_response_code($code);
        // set the header to make sure cache is forced
        header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");
        // treat this as json
        header('Content-Type: application/json');
        $status = array(
            200 => '200 OK',
            400 => '400 Bad Request',
            403 => 'Feild Required',
            422 => 'Unprocessable Entity',
            500 => '500 Internal Server Error'
        );
        // ok, validation error, or failure
        // echo header('Status: '.$status[$code]);
        // return the encoded json
        return json_encode(array(
            'status' => $code, // < 300, // success or not?
            'response' => $message,
            'comment'=> $comment
        ));
    }
    
    //Password Compare
    public function passwordCompare($str1, $str2){
        return strcmp($str1,$str2) == 0 ? true : false;
    }

    //Encrypt password
    public function passwordEncryption($password){
        return md5($password);
    }

    //Token Generator
    public function generateToken($email){
        $key =  random_bytes(128);
        return hash_hmac('sha512', $email, $key);
    }

    //User validation function
    public function userValidation(Request $request){
        $obj = new helperClass();
        $content = $request->getHeader("content");
        $lan = $request->getHeader("lan");
        $Beraer = $request->getHeader('auth');
        $userid = $request->getAttribute('userid');

        $token= "";
        $contenttype="";
        $language="";
        foreach ($Beraer as $header => $value) {
            $token = $value;
        }

//        if (!(strpos($token, 'Bearer ') !== false)) {
//            echo $obj->json_response(0, "message", "Token is not in a correct format!");
//            return;
//        }

        if (preg_match('/Bearer\s(\S+)/', $token, $matches)) {
            $token = $matches[1];
        }

        if (!$obj-> CheckToken($token)){
            echo $obj->json_response(0, "message", "Bad Request!");
            return;
        }
        foreach ($content as $header => $value){
            $contenttype = $value;
        }
        if($contenttype != "application/json"){
            echo $obj->json_response(0, "message", "Invalid Content type!");
            return;
        }

        foreach ($lan as $header => $value){
            $language = $value;
        }

        if (($language !== "Language_KH") && ($language !== "Language_EN") ){
            echo $obj->json_response(0, "message", "Invalid language");
            return;
        }

//        foreach ($userid as $header => $value) {
//            $users = $value;
//        }

        $sql ="SELECT *,userid as id FROM v_getusers WHERE userid=:i AND api_token=:a";

        $conn = $obj->connection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':i', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':a', $token, PDO::PARAM_LOB);
        $stmt->execute();
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)){
            if ($result['id']){
                return $result;
            }
        }
        echo $obj->json_response(0, "message", "Bad Request!");
        return;
    }

    public function CheckToken($Token){
        $obj = new helperClass();
        $sql ="SELECT count(*) as num FROM v_getusers WHERE api_token=:a";
        $conn = $obj->connection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':a', $Token, PDO::PARAM_STR);
        $stmt->execute();
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)){
            if ($result['num']){
                return true;
            }
            return false;
        }
    }

    //User Upload Image
    public function uploadImage(){
        $target_dir = $_SERVER['DOCUMENT_ROOT']."/images/";
        $obj = new helperClass();
        $target_file = $target_dir . basename($_FILES["photo"]["name"]);
        $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

        // Check if image file is a actual image or fake image
        if(isset($_POST["submit"])) {
            $check = getimagesize($_FILES["photo"]["tmp_name"]);
            if ($check !== false) {
                echo $obj->json_response(0, "message", "File is an image - " . $check["mime"] . ".");
                return;
            } else {
                echo $obj->json_response(0, "message", "File is not an image.");
                return;
            }
        }

        // Check if file already exists
        if (file_exists($target_file)) {
            echo $obj->json_response(0, "message", "Sorry, file already exists.");
            return;
        }
        // Check file size
        if ($_FILES["photo"]["size"] > 10000000) {
            echo $obj->json_response(0, "message",  "Sorry, your file is too large.");
            return;
        }
        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif" ) {
            echo $obj->json_response(0, "message",  "Sorry, only JPG, JPEG, PNG & GIF files are allowed.");
            return;
        }

        $temp = explode(".", $_FILES["photo"]["name"]);
        $newfilename = round(microtime(true)) . '.' . end($temp);
        $target_file = $target_dir . $newfilename;

        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            return $newfilename;
            //return "http://".$_SERVER['SERVER_NAME']."/images/".$newfilename;
        } else {
            echo $obj->json_response(0, "message",   "Sorry, there was an error uploading your file.");
            return;
        }
    }

    public function isPostOfUser($postid, $userid){
        $obj = new helperClass();
        $sql = "SELECT count(*) as postcount FROM tblposts WHERE postid = :postid AND userid = :userid";
        $conn = $obj->connection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':postid', $postid, PDO::PARAM_STR);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->execute();
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)){
            if ($result['postcount']){
                return true;
            }
        }
        return false;
    }

    public function isPostExist($key,$value){
        $obj = new helperClass();
        $sql = "SELECT count(*) as postcount FROM tblposts WHERE $key = :id AND isdelete = 0";
        $conn = $obj->connection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $value, PDO::PARAM_STR);
        $stmt->execute();
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)){
            if ($result['postcount']){
                return true;
            }
        }
        return false;
    }

    public function isUserExist($key,$value){
        $obj = new helperClass();
        $sql = "SELECT count(*) as usercount FROM `v_getusers` WHERE $key = :id";
        $conn = $obj->connection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $value, PDO::PARAM_STR);
        $stmt->execute();
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)){
            if ($result['usercount']){
                return true;
            }
        }
        return false;
    }
    /**
     * Get hearder Authorization
     * */
    function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['auth'])) {
            $headers = trim($_SERVER["auth"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['auth'])) {
                $headers = trim($requestHeaders['auth']);
            }
        }
        return $headers;
    }
    /**
     * get access token from header
     * */
    function getBearerToken() {
    $headers = $this->getAuthorizationHeader();
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
            return $matches[1];
        }
    }
    return null;
}

    //Get Posts by postid
    public function getComment($postid){
        $sql = "SELECT * FROM  `v_getusercomment` WHERE postid = $postid";
        $conn = $this->connection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $arr = Array();
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)){
            array_push($arr,$result);
        };
        return $arr;
    }
}