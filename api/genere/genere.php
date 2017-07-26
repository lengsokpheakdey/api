<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/api/genere/dara', function ( Request $request, Response $response, $args) {
    echo "hello genere";
});