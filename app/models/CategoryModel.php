<?php

class CategoryModel {
	
    private $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    public function GetCategories($category_id = NULL, $searchPairs = []) {
	
        if(isset($category_id))
            $searchPairs = array_merge(["category.category_id" => $category_id], $searchPairs);

        if(count($searchPairs) > 0)
            $where[ "AND" ] = $searchPairs;

        $whereClause = $this->db->where_clause($where);

        $categories = $this->db->query("
            SELECT
	            category.*,
	            currency_name,
	            COALESCE(total, 0) AS total
            FROM category
            LEFT JOIN (
                SELECT 
                    category.*,
                    currency_name,
                    COALESCE(SUM(in_amount), 0) - COALESCE(SUM(out_amount), 0) AS total
                FROM category
                LEFT JOIN transaction ON transaction.category_id=category.category_id OR category.category_id IS NULL
                JOIN currency ON transaction.in_currency_id=currency.currency_id OR transaction.out_currency_id=currency.currency_id
                $whereClause
                GROUP BY category.category_id, currency_id)
            t1 ON t1.category_id=category.category_id
            ORDER BY category.order
        ")->fetchAll(PDO::FETCH_ASSOC);
        
        $transformedCategories = [];
        foreach($categories as $category) {
            if(!$transformedCategories[$category["category_id"]])
                $transformedCategories[$category["category_id"]] = [
                        "category_id" => $category["category_id"],
                        "category_name" => $category["category_name"],
                        "parent_category_id" => $category["parent_category_id"],
                        "icon" => $category["icon"],
                        "order" => $category["order"]
                    ];
            if($category["total"] != 0)
                $transformedCategories[$category["category_id"]]["totals"][] = [
                        "currency_name" => $category["currency_name"],
                        "total" => $category["total"]
                    ]; 
        }

        return array_values($transformedCategories);
    }
    
    public function UpdateCategory($category_id, $values) {
        $ret = $this->db->update("category",
                $values,
                [
                    "category_id" => $category_id
                ]);
        return $ret;
    }
}