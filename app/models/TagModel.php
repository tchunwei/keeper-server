<?php

class TagModel {
	
    private $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    public function CreateTag($tag_name) {
        return $this->db->insert("tag",
                [
                    "tag_name" => $tag_name
                ]);
    }
    
    public function GetTags($tag_id = NULL, $searchPairs = []) {
	
        if(isset($tag_id))
            $searchPairs = array_merge(["tag_id" => $tag_id], $searchPairs);
        
        $tags = $this->db->select("tag",
                "*",
                count($searchPairs) > 0 ? [ "AND" => $searchPairs] : NULL);
        
        return $tags;
    }
}