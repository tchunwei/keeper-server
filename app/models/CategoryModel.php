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
                COALESCE(SUM(sum_in) - SUM(sum_out), 0) AS total
            FROM
            (SELECT * FROM
                (
                    SELECT 
                        category_id,
                        in_currency_id AS currency_id,
                        COALESCE(SUM(in_amount), 0) AS sum_in,
                        0 AS sum_out
                    FROM transaction
                    $whereClause
                    GROUP BY category_id, in_currency_id
                UNION
                    SELECT 
                        category_id,
                        out_currency_id  AS currency_id,
                        0 AS sum_in,
                        COALESCE(SUM(out_amount), 0) AS sum_out
                    FROM transaction
                    $whereClause
                    GROUP BY category_id, out_currency_id
                ) t
                WHERE sum_in <> 0 OR sum_out <> 0
            ) t1
            RIGHT JOIN category ON category.category_id=t1.category_id
            LEFT JOIN currency ON currency.currency_id=t1.currency_id
            GROUP BY category.category_id, currency_name
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