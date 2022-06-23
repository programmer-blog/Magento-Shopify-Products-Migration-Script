<?php
  namespace App;
  use GuzzleHttp\Client;

  class MagentoProducts {
    private $base_url;
    private $login_endpoint;
    private $products_endpoint;
    private $username;
    private $password;

    public function __construct($base_url, $products, $login, $username, $password)
    {
      $this->base_url = $base_url;
      $this->login_endpoint = $login;
      $this->products_endpoint = $products;
      $this->username = $username;
      $this->password = $password;
      $this->client = $this->get_token();
    }

    /**
     * Login to magento store and return token
     */
    private function get_token() {
      $client = new Client();
      $headers = ['Content-Type' => 'application/json', 'Accept' => 'application/json'];
      $data = array(
        'username' => $this->username, 
        'password'=> $this->password
      );
      $token = $client->post($this->base_url.$this->login_endpoint, [
        'headers' => $headers,
        'body' => json_encode($data)
      ]);

      return $token->getBody()->getContents();
    }

    /**
     * Get products from magento store
     */
    private function get_products() {
      $client = new Client();
      $token = trim($this->get_token(), '"');
      $headers = [
        'Content-Type' => 'application/json', 
        'Accept' => 'application/json',
        'Authorization' => "Bearer $token"
      ];
      $response = $client->get($this->base_url.$this->products_endpoint, [
        'headers' => $headers
      ]);

      return $response->getBody()->getContents();
    }

    /**
     * After getting results from magento
     * Process results according to shopify store 
     */
    public function processResults() {
      $productsArr = json_decode($this->get_products());
      $results = [];

      foreach($productsArr as $products) {
        foreach($products as $product) {
          $data = [];
          if(isset($product->name)){
            $data['product']['title'] = $product->name;
            $data['product']['body_html'] = $product->custom_attributes[16]->value;
            array_push($results, $data);
          }
        }
      }
      return $results;
    }
  }
