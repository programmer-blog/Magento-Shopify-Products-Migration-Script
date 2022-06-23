<?php
  namespace App;
  use GuzzleHttp\Client;

  class AddToShopify {
    private $base_url;
    private $version;
    private $resource;
    private $key;
    private $secret;
    private $https;

    public function __construct($base_url, $version, $resource, $key, $secret, $https)
    {
      $this->base_url = $base_url;
      $this->version = $version;
      $this->resource = $resource;
      $this->key = $key;
      $this->secret = $secret;
      $this->https = $https;
    }

    /**
     * Add products to shopify store
     */
    public function add_product($products) {
      $client = new Client();
      $headers = ['Content-Type' => 'application/json', 'Accept' => 'application/json'];
      $url = $this->https.$this->key.':'.$this->secret.'@'.$this->base_url.$this->version.$this->resource;
      foreach($products as $product) {
        $result[] = $client->post($url, [
          'headers' => $headers,
          'body' => json_encode($product)
        ]);
      }
      return $result;
    }
  }
