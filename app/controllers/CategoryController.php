<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class CategoryController extends \Slim\Container {
	
    private $view;
    private $model;
    
    public function __construct($view, $db)
    {
        $this->view = $view;
        $this->model = new CategoryModel($db);
    }
    
    public function GetCategories(Request $request, Response $response) {
        $id = $request->getAttribute("id");
        $searchPairs = $request->getQueryParams();
        
        // handle those > / < / >= / <= in query param
        foreach($searchPairs as $key => $value) {
            if(preg_match("/[><]/", $key, $match)) {
                unset($searchPairs[$key]);
                $tokens = explode($match[0], $key);
                $op = "[".$match[0].($value ? "=" : "")."]";
                $searchPairs[$tokens[0].$op] = $value ? $value : $tokens[1];
            }
        }

        $response->getBody()->write(json_encode($this->model->GetCategories($id, $searchPairs)));

        $response = $response->withHeader('Content-type', 'application/json');
        return $response;
    }
    
    public function UpdateCategories(Request $request, Response $response) {
        $categories = $request->getParsedBody()["categories"];
        
        foreach($categories as $category) {
            $category_id = $category["category_id"];
            unset($category["category_id"]);
            if(!$category["parent_category_id"]) // else is empty string, will cause problem
                $category["parent_category_id"] = NULL;
            $this->model->UpdateCategory($category_id, $category);
        }
        
        return $response;
    }
    
    public function ListCategories(Request $request, Response $response) {
		
        return $this->view->render($response, 'categories/list.twig');
    }
}