<?php
  error_reporting(~E_ALL);
  require_once('src/config.php');
  require_once('vendor/autoload.php');
  use \App\
  {
    AddToShopify,
    MagentoProducts
  };

  $productObj = new MagentoProducts(
      MAGENTO_BASE_URL, 
      MAGENTO_PRODUCTS_URL, 
      MAGENTO_TOKEN_URL, 
      MAGENTO_ADMIN_USERNAME, 
      MAGENTO_ADMIN_PASSWORD,
      MAGENTO_PRODUCT_IMAGE_URL
    );

  $addProducts = new AddToShopify(
    SHOPIFY_BASE_URL,
    SHOPIFY_API_ENDPOINT_V,
    SHOPIFY_API_PRODUCTS,
    SHOPIFY_API_KEY,
    SHOPIFY_API_SECRET,
    HTTPS
  );

  $products = $productObj->processResults();
  $results = $addProducts->add_product($products);

  $output = "<h2>Products Inserted to Shopify from Magento</h2><br /><br />";
  foreach($results as $result) {
    $productInfo = json_decode($result->getBody()->getContents());
    $output .= 'Product ID: <strong>'.$productInfo->product->id.'</strong> Title: <strong>'.$productInfo->product->title.'</strong> <br />';
  }

  echo $output;