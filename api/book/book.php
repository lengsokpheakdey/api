<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/api/book/hellobook/{id}', function (Request $request, Response $response, $args) {
    echo "hello book";
    echo $request->getAttribute('id');
});