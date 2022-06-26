<?php
  namespace App;
  use GuzzleHttp\Client;

  class MagentoProducts {
    private $base_url;
    private $login_endpoint;
    private $products_endpoint;
    private $image_endpoint;
    private $username;
    private $password;

    public function __construct($base_url, $products, $login, $username, $password, $image_endpoint)
    {
      $this->base_url = $base_url;
      $this->login_endpoint = $login;
      $this->products_endpoint = $products;
      $this->image_endpoint = $image_endpoint;
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
      $storeProducts = json_decode($this->get_products());
      $response = [];
      $results = $storeProducts->items;
      foreach($results as $product) {
        $data = [];
        if(isset($product->name)) {
          $data['product']['title'] = $product->name;
          $data['product']['body_html'] = $product->custom_attributes[16]->value;
          $data['product']['variants'][] = array(
                'option1'=> 'Primary',
                'sku'=> $product->sku,
                'price' => $product->price
              ); 
          $data['product']['id'] = $product->id;
          $data['product']['updated_at'] = date("Y-m-d", strtotime($product->updated_at));
          /**
           * This code is memory intensive because a product image from megneto is copied and
           * Converted to base64 encoding and saved to Shopify database
           */
          if(count($product->media_gallery_entries) && isset($product->media_gallery_entries[0])){
              $image = $product->media_gallery_entries[0];
              if($image->media_type == 'image') {
                $imagedata = file_get_contents($this->base_url.$this->image_endpoint.$image->file);
                $base64OfImage = base64_encode($imagedata);
                $data['product']["images"] = 
                array(
                  array(
                      "attachment" => $base64OfImage
                  )
                );
              }
            }
            array_push($response, $data);
          }
        }
        return $response;
      }
    }
