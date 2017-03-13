<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class TransactionController extends \Slim\Container {
	
    private $view;
    private $model;
    
    public function __construct($view, $db)
    {
        $this->view = $view;
        $this->model = new TransactionModel($db);
    }
    
    public function CreateTransaction(Request $request, Response $response) {
        $id = $this->model->CreateTransaction();
        $this->model->UpdateTransaction($id, $request->getParsedBody());
        
        return $response;
    }
    
    public function GetTransactions(Request $request, Response $response) {
        $id = $request->getAttribute("id");
        $searchPairs = $request->getQueryParams();
        unset($searchPairs["_"]); // added by datatables
        
        // handle those > / < / >= / <= in query param
        foreach($searchPairs as $key => $value) {
            if(preg_match("/[><]/", $key, $match)) {
                unset($searchPairs[$key]);
                $tokens = explode($match[0], $key);
                $op = "[".$match[0].($value ? "=" : "")."]";
                $searchPairs[$tokens[0].$op] = $value ? $value : $tokens[1];
            }
        }
 
        $response->getBody()->write(json_encode($this->model->GetTransactions($id, $searchPairs)));
        
        $response = $response->withAddedHeader('Content-Type', 'application/json');
        $response = $response->withAddedHeader('Access-Control-Allow-Origin', '*');
        return $response;
    }
    
    public function UpdateTransaction(Request $request, Response $response) {
        $id = $request->getAttribute("id");
        $this->model->UpdateTransaction($id, $request->getParsedBody());
        
        return $response;
    }
    
    public function DeleteTransaction(Request $request, Response $response) {
        $id = $request->getAttribute("id");
        $this->model->DeleteTransaction($id);
        
        return $response;
    }
    
    public function ListTransactions(Request $request, Response $response) {
		
        return $this->view->render($response, 'transactions/list.twig');
    }
    
    public function AddEditTransactions(Request $request, Response $response) {
		
        return $this->view->render($response, 'transactions/edit.twig', [
            "id" => $request->getAttribute("id")
        ]);
    }
}