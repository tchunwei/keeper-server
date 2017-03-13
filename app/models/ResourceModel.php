<?php

class ResourceModel {
	
    private $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    public function GetResources($resource_id = NULL, $searchPairs = []) {
	
        if(isset($category_id))
            $searchPairs = array_merge(["resource_id" => $resource_id], $searchPairs);
        
        $resources = $this->db->select("resource",
                "*",
                count($searchPairs) > 0 ? [ "AND" => $searchPairs] : NULL);
        
        return $resources;
    }
    
    public function GetBalance() {
        
        return $this->db->query("
            SELECT * FROM 
                (WITH 
                    total_in AS (
                        SELECT
                            SUM(in_amount) AS amount,
                            in_resource_id AS resource_id,
                            in_currency_id AS currency_id
                        FROM transaction
                        WHERE in_resource_id IS NOT NULL AND in_currency_id IS NOT NULL
                        GROUP BY 
                            in_resource_id, in_currency_id
                    ),
                    total_out AS (
                        SELECT
                            SUM(out_amount) AS amount,
                            out_resource_id AS resource_id,
                            out_currency_id AS currency_id
                        FROM transaction AS expense
                        WHERE out_resource_id IS NOT NULL AND out_currency_id IS NOT NULL
                        GROUP BY 
                            out_resource_id, out_currency_id
                    )
                SELECT 
                    COALESCE(total_in.amount, 0) - COALESCE(total_out.amount, 0) AS balance,
                    COALESCE(total_in.resource_id, total_out.resource_id) AS resource_id,
                    COALESCE(total_in.currency_id, total_out.currency_id) AS currency_id
                FROM total_in 
                FULL JOIN total_out ON total_in.resource_id=total_out.resource_id AND total_in.currency_id=total_out.currency_id) AS balance
            JOIN resource ON resource.resource_id=balance.resource_id
            JOIN currency ON currency.currency_id=balance.currency_id
            ORDER BY balance.resource_id
        ")->fetchAll(PDO::FETCH_ASSOC);
    }
}