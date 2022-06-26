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
    HTTPS,
    SHOPIFY_API_METAFIELDS
  );

  $products = $productObj->processResults();
  $results = $addProducts->add_product($products);

  $output = "<h2>Products Inserted / Updated - Shopify Store</h2><br /><br />";
  foreach($results as $productInfo) {
    $output .= 'Product ID: <strong>'.$productInfo->product->id.'</strong> Title: <strong>'.$productInfo->product->title.'</strong> <br />';
  }

  echo $output;