<?php

class MultiUpload
{
    public function uploadImage(){
        extract($_POST);
        $error=array();
        $target_dir = $_SERVER['DOCUMENT_ROOT']."/images/";
        $filenames = array();
        extract($_POST);
        $error=array();
        $extension=array("jpeg","jpg","png","gif","JPG");
        foreach ($_FILES["files"]["tmp_name"] as $key => $tmp_name)
        {
            $file_name = $_FILES["files"]["name"][$key];
            $file_tmp = $_FILES["files"]["tmp_name"][$key];
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);
            if (in_array($ext, $extension)) {
                if (!file_exists($target_dir . $file_name)) {
                    move_uploaded_file($file_tmp = $_FILES["files"]["tmp_name"][$key], $target_dir . $file_name);
                }
                else {
                    //$filename = basename($file_name, $ext);
                    //$newFileName = $filename . time() . "." . $ext;
                    unlink($target_dir . $file_name);
                    move_uploaded_file($file_tmp = $_FILES["files"]["tmp_name"][$key], $target_dir . $file_name);
                }
                array_push($filenames,"$file_name, ");
            } else {
                array_push($error, "$file_name, ");
            }
        }
        var_dump($filenames);
    }
}