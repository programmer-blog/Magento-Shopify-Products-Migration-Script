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
    private $metafields;

    public function __construct($base_url, $version, $resource, $key, $secret, $https, $metafields)
    {
      $this->base_url = $base_url;
      $this->version = $version;
      $this->resource = $resource;
      $this->key = $key;
      $this->secret = $secret;
      $this->https = $https;
      $this->metafields = $metafields;
    }

    /**
     * Add products and metafield to shopify store
     * Check if product already exists in shopify then update else insert
     * If updated_at of the magento product is today then update otherwise donot udpate the product
     */
    public function add_product($products) {
      $client = new Client();
      $headers = ['Content-Type' => 'application/json', 'Accept' => 'application/json'];
      $base_url = $this->https.$this->key.':'.$this->secret.'@'.$this->base_url;
      $products_url = $base_url.$this->version.$this->resource;
      $results = [];

      foreach($products as $product) {
        $magento_product_id = $product['product']['id'];
        $magento_updated_at = $product['product']['updated_at'];
        unset($product['product']['id']);
        unset($product['product']['updated_at']);
        $shopify_product_id = $this->checkIfAlreadyExists($magento_product_id);

        if(!$shopify_product_id) {
          $data = $client->post($products_url, [
            'headers' => $headers,
            'body' => json_encode($product)
          ]);
          $result = json_decode($data->getBody()->getContents());
          $metafieldResult = $this->add_metafield($result->product->id, $magento_product_id);
          array_push($results, $result);
        } else {
          if($magento_updated_at == date('Y-m-d')) { //if product is updated today - business logic?
            $update_url = $base_url.'/admin/products/'.$shopify_product_id.'.json';
            $data = $client->put($update_url, [
              'headers' => $headers,
              'body' => json_encode($product)
            ]);
            $result = json_decode($data->getBody()->getContents());
            array_push($results, $result);
          }
        }
      }
      return $results;
    }

    /**
     * Check if the product already exists in shopify
     * Get products from shopify
     * Get metafields of product
     * Compare metafield value with magento product id, if matches return True and break the loop else continue to next
     */
    private function checkIfAlreadyExists($megento_product_id) {
      $client = new Client();
      $base_url =  $this->https.$this->key.':'.$this->secret.'@'.$this->base_url.$this->version;
      $data = $client->get($base_url.$this->resource);
      $results = json_decode($data->getBody()->getContents());

      foreach($results->products as $product) {
        $metafield_url = str_replace("{{product_id}}", $product->id, $this->metafields);
        $metafield_data = $client->get($base_url.$metafield_url);
        $metafields_results = json_decode($metafield_data->getBody()->getContents());
        foreach($metafields_results->metafields as $metafield) {
          if($metafield->value == $megento_product_id) {
            return $product->id; 
          }
        }
      }
      return false;
    }

    /**
     * Add magento product id as metafield in shopify
     */
    private function add_metafield($product_id, $magento_product_id) {
      $client = new Client();
      $headers = ['Content-Type' => 'application/json', 'Accept' => 'application/json'];
      $metafield_url = str_replace("{{product_id}}", $product_id, $this->metafields);
      $url = $this->https.$this->key.':'.$this->secret.'@'.$this->base_url.$this->version.$metafield_url;
      $metafield = array(
          "metafield" => array (
              "namespace" => "magento_product",
              "key"=> "magento_product_id",
              "value"=> $magento_product_id ,
              "type"=> "integer"
          )
      );

      $result = $client->post($url, [
        'headers' => $headers,
        'body' => json_encode($metafield)
      ]);

      return $result;
    }
  }
