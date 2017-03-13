<?php

$app->group("/transactions", function() {
    // UI
    $this->get("/list", "TransactionController:ListTransactions");
    $this->get("/add", "TransactionController:AddEditTransactions");
    $this->get("/{id}/edit", "TransactionController:AddEditTransactions");
    
    // REST API
    $this->get("/", "TransactionController:GetTransactions");
    $this->put("/", "TransactionController:CreateTransaction");
    $this->get("/{id}", "TransactionController:GetTransactions");
    $this->post("/{id}", "TransactionController:UpdateTransaction");
    $this->delete("/{id}", "TransactionController:DeleteTransaction");
});

$app->group("/categories", function() {
    // UI
    $this->get("/list", "CategoryController:ListCategories");
    
    // REST API
    $this->get("/", "CategoryController:GetCategories");
    $this->get("/{id}", "CategoryController:GetCategories");
    $this->post("/", "CategoryController:UpdateCategories");
});

$app->group("/resources", function() {
    // UI
    
    // REST API
    $this->get("/balance", "ResourceController:GetBalance");
    $this->get("/", "ResourceController:GetResources");
    $this->get("/{id}", "ResourceController:GetResources");
});

$app->group("/currencies", function() {
    // UI
    
    // REST API
    $this->get("/", "CurrencyController:GetCurrencies");
    $this->get("/{id}", "CurrencyController:GetCurrencies");
});

$app->group("/tags", function() {
    // UI
    
    // REST API
    $this->get("/", "TagController:GetTags");
    $this->get("/{id}", "TagController:GetTags");
});