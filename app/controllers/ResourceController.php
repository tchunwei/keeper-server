<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class ResourceController extends \Slim\Container {
	
    private $view;
    private $model;
    
    public function __construct($view, $db)
    {
        $this->view = $view;
        $this->model = new ResourceModel($db);
    }
    
    public function GetResources(Request $request, Response $response) {
        $id = $request->getAttribute("id");
        $response->getBody()->write(json_encode($this->model->GetResources($id)));
        
        $response = $response->withHeader('Content-type', 'application/json');
        return $response;
    }
    
    public function GetBalance(Request $request, Response $response) {
        $response->getBody()->write(json_encode($this->model->GetBalance()));
        
        $response = $response->withHeader('Content-type', 'application/json');
        return $response;
    }
}