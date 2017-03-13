<?php

class CurrencyModel {
	
    private $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    public function GetCurrencies($currency_id = NULL, $searchPairs = []) {
	
        if(isset($currency_id))
            $searchPairs = array_merge(["currency_id" => $currency_id], $searchPairs);
        
        $currencies = $this->db->select("currency",
                "*",
                count($searchPairs) > 0 ? [ "AND" => $searchPairs] : NULL);
        
        return $currencies;
    }
}