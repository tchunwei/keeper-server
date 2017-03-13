<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class CurrencyController extends \Slim\Container {
	
    private $view;
    private $model;
    
    public function __construct($view, $db)
    {
        $this->view = $view;
        $this->model = new CurrencyModel($db);
    }
    
    public function GetCurrencies(Request $request, Response $response) {
        $id = $request->getAttribute("id");
        $response->getBody()->write(json_encode($this->model->GetCurrencies($id)));
        
        $response = $response->withHeader('Content-type', 'application/json');
        return $response;
    }
}