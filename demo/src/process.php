<?php 

// PHP SDK Demo App
// Copyright 2017 Optimizely. Licensed under the Apache License
// View the documentation: http://bit.ly/2rfsbxt

  require_once 'vendor/autoload.php';
  use Optimizely\Optimizely;
  use Optimizely\Logger\DefaultLogger;

  $PROJECT_ID = '<project_id>';


  if(isset($_POST['track'])){
    track($_POST['track']);
  }


  function initOpti(){
    $project_id = $GLOBALS['PROJECT_ID'];
    $url = "https://cdn.optimizely.com/json/$project_id.json";
    $datafile = file_get_contents($url);
    //$optimizelyClient = new Optimizely($datafile, null, new DefaultLogger(logger.DEBUG));
    $optimizelyClient = new Optimizely($datafile);

    return $optimizelyClient;
  }

  function track($user_id){
    $client = initOpti();
    $client->track('BUY', $user_id);

    header('Location: purchase.html');
  }


  function getItems($user_id = NULL){
    $csvFile = file('items.csv');
    $data = [];
    foreach ($csvFile as $line) {
      $data[] = str_getcsv($line);
    }

    if(isset($user_id)){
      $client = initOpti();

      $variation = $client->activate('ITEM_SORT', $user_id);
    
      if ($variation == 'PRICE') {
        echo "<h3>PRICE</h3>";
        foreach ($data as $key => $row) {
          $num = str_replace('$', '', $row[3]);
          $num = (int)$num;
          $price[$key] = $num;
        }
      
        array_multisort($price, SORT_ASC, $data);
      
      } elseif ($variation == 'CATEGORY') {  
          echo "<h3>CATEGORY</h3>";
          foreach ($data as $key => $row) {
            $category[$key] = $row[2];
           }
        
          array_multisort($category, SORT_ASC, $data);
      } 
      return array($data, $variation);

    } else {		
      return array($data, NULL);
    }
  }

  function displayItems($items){
    
    for($i=0;$i<9;$i++){
      $item_name = $items[$i][0];
      $item_color = $items[$i][1];
      $item_category = $items[$i][2];
      $item_price = $items[$i][3];
      $item_url = $items[$i][4];

      echo "<td class='background--white text--center font-family--tahoma'>";
      echo "<h2><b> $item_name</b></h2>";
      echo " in $item_color <br>";
      echo "<b>$item_category, $item_price </b>";
      echo "<img src='images/$item_url'>";
      echo "<form action='process.php' method='post'> 
           <input type='text' name='track' value='$user_id' hidden='true'>";
      echo "<button type='submit' style='background: #cb1b2c; color: #ffffff; border: none'> BUY NOW</button>";
      echo "</form>";
      echo "</td>";
      echo "<td width='10'></td>";
      echo "<p></p>";
      // every 3 rows
      if (($i+1) % 3 == 0) {
      echo "</tr>";
      }
    }    
  }

?>