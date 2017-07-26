<?php

require '../vendor/autoload.php';
require_once ('../config/helperClass.php');
require_once ('../api/comments/MultiUpload.php');

$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);

//require '../api/book/book.php';
//require '../api/genere/genere.php';

require '../api/users/users.php';
require '../api/posts/posts.php';
require '../api/comments/comments.php';

$app->run();
