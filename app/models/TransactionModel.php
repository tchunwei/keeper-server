<?php

class TransactionModel {
	
    private $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    public function CreateTransaction() {
        return $this->db->insert("transaction", [ "description" => NULL ]);
    }
    
    public function GetTransactions($transaction_id = NULL, $searchPairs = []) {
	
        if(isset($transaction_id))
            $searchPairs = array_merge(["transaction_id" => $transaction_id], $searchPairs);
        
        
        $transactions = $this->db->select("transaction",
                [ 
                    "[>]resource(in_resource)" => [ "in_resource_id" => "resource_id" ],
                    "[>]resource(out_resource)" => [ "out_resource_id" => "resource_id" ],
                    "[>]currency(in_currency)" => [ "in_currency_id" => "currency_id" ],
                    "[>]currency(out_currency)" => [ "out_currency_id" => "currency_id" ],
                    "[>]category" => "category_id"
                ],
                [
                    "transaction_id",
                    "in_resource_id",
                    "in_resource.resource_name (in_resource_name)",
                    "in_resource.background_color (in_resource_color)",
                    "in_resource.icon (in_resource_icon)",
                    "out_resource_id",
                    "out_resource.resource_name (out_resource_name)",
                    "out_resource.background_color (out_resource_color)",
                    "out_resource.icon (out_resource_icon)",
                    "in_currency_id",
                    "in_currency.currency_name (in_currency_name)",
                    "out_currency_id",
                    "out_currency.currency_name (out_currency_name)",
                    "category_id",
                    "category_name",
                    "category.background_color (category_color)",
                    "category.icon (category_icon)",
                    "in_amount",
                    "out_amount",
                    "made_on",
                    "description"
                ],
                count($searchPairs) > 0 ? [ "AND" => $searchPairs, "ORDER" => ["made_on" => "DESC"]] : [ "ORDER" => ["made_on" => "DESC"] ]);
        
        $tags = $this->db->select("transaction_tag",
                [
                    "[>]transaction" => "transaction_id",
                    "[>]tag" => "tag_id"
                ],
                [
                    "transaction_id",
                    "tag_id",
                    "tag_name"
                ],
                [
                    "transaction_id" => array_column($transactions, "transaction_id")
                ]);
            
        foreach($transactions as &$transaction) {
            foreach($tags as $i => &$tag) {
                if($transaction["transaction_id"] === $tag["transaction_id"]) {
                    $transaction["tags"][] = [ 
                        "tag_id" => $tag["tag_id"],
                        "tag_name" => $tag["tag_name"]
                    ];
                    unset($tags[$i]);
                }
            }
        }
        
        $items = $this->db->select("item",
            "*",
            [
                "transaction_id" => array_column($transactions, "transaction_id")
            ]
        );
        foreach($transactions as &$transaction) {
            foreach($items as $i => &$item) {
                if($transaction["transaction_id"] === $item["transaction_id"]) {
                    $transaction["items"][] = [ 
                        "item_id" => $item["item_id"],
                        "description" => $item["description"],
                        "in_amount" => $item["in_amount"],
                        "out_amount" => $item["out_amount"]
                    ];
                    unset($items[$i]);
                }
            }
        }
        
        return $transactions;
    }
    
    public function UpdateTransaction($transaction_id, $values) {
        
        $ret = $this->db->update("transaction",
                [
                    "category_id" => $values["category_id"],
                    "made_on" => $values["made_on"],
                    "in_resource_id" => $values["in_resource_id"],
                    "out_resource_id" => $values["out_resource_id"],
                    "in_currency_id" => $values["in_currency_id"],
                    "out_currency_id" => $values["out_currency_id"],
                    "in_amount" => $values["in_amount"],
                    "out_amount" => $values["out_amount"],
                    "description" => $values["description"]
                ],
                [
                    "transaction_id" => $transaction_id
                ]);
        
        $ret = $ret && $this->db->delete("transaction_tag",
            [
                "AND" => [
                    "tag_id[!]" => $values["tags"] ? $values["tags"] : 0,
                    "transaction_id" => $transaction_id
                ]
            ]);
        if($values["tags"]) {
            foreach($values["tags"] as $tag) {
                if(!is_numeric($tag) || $this->db->count("tag", ["tag_id" => $tag]) == 0) {
                    $tag = (new TagModel($this->db))->CreateTag($tag);
                }
                if($this->db->count("transaction_tag", ["AND" => ["tag_id" => $tag, "transaction_id" => $transaction_id]]) === 0) {
                    $this->db->insert("transaction_tag",
                            [
                                "transaction_id" => $transaction_id,
                                "tag_id" => $tag
                            ]);
                }
            }
        }
        
        
        $this->db->delete("item",
                [
                    "AND" => [
                        "item_id[!]" => $values["items"] ? array_column($values["items"], "item_id") : 0,
                        "transaction_id" => $transaction_id
                    ]
                ]);
        if($values["items"]) {
            foreach($values["items"] as $item) {
                if($item["item_id"] == 0) {
                    $item["item_id"] = $this->db->insert("item",
                            [
                                "transaction_id" => $transaction_id
                            ]);
                }
                $this->db->update("item",
                        [
                            "description" => $item["description"],
                            "in_amount" => $item["in_amount"],
                            "out_amount" => $item["out_amount"]
                        ],
                        [
                            "item_id" => $item["item_id"]
                        ]);
            }
        }
            
        return $ret;
    }
    
    public function DeleteTransaction($transaction_id) {
        return $this->db->delete("transaction", [ "transaction_id" => $transaction_id ]);
    }
}