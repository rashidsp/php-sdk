<?php

namespace App\controllers;

require_once '../../vendor/autoload.php';
use Optimizely\Optimizely;
use Optimizely\Logger\LoggerInterface;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
class DemoController
{
    public function __construct($logger) {
        $this->session = new \SlimSession\Helper;
        $this->logger = $logger;
    }

    public function getConfig($request, $response, $args)
    {
        $config = $this->session->config;
        $configArray = array(
            'project_id'=> $config['project_id'],
            'experiment_key'=> $config['experiment_key'],
            'event_key'=> $config['event_key'],
            'project_configuration_json'=> json_decode($config['project_configuration_json'])
        );
        return $response->withJson($configArray);
    }

    public function create($request, $response, $args)
    {
        $params = $request->getParams();
		$project_id = $params['project_id'];
		$url="https://cdn.optimizely.com/json/".$project_id.".json";
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if($httpcode == 200){
            $config = array(
                'project_id'=> $project_id,
                'experiment_key'=> $params['experiment_key'],
                'event_key'=> $params['event_key'],
                'project_configuration_json'=> $data
            );

            $this->session->set('config', $config);

            $config['project_configuration_json'] = json_decode($data);
            $config = array('status' => '0') + $config;
            return $response->withJson($config);
        }else{
            return $response->withJson(['status'=> '1']);
        }
    }
    public function selectVisitor($request, $response, $args)
    {
        $params = $request->getParams();
        $visitor = $params['user_id'];

        if (!$visitor) return $response->withJson(['status'=> '1','message'=>'vistor ID can not be empty.']);
        $config = $this->session->config;
        if (!$config) return $response->withJson(['status'=> '1','message'=>'project ID does not exist.']);
        if (!$config['project_configuration_json']) return $response->withJson(
            ['status'=> '1','message'=>'Datafile can not be empty.']
        );
        $optimizely_service = new OptimizelyService($config['project_configuration_json']);
        $variation = $optimizely_service->activate($visitor, $config['experiment_key']);
        $products = Product::getProducts();
        if ($variation == "sort_by_name"){
            $sortArray = $this->sortProducts("name", $products);
            array_multisort($sortArray['name'],SORT_ASC,$products);
        }elseif ($variation == "sort_by_price"){
            $sortArray = $this->sortProducts("price", $products);
            array_multisort($sortArray['price'],SORT_ASC,$products);
        }
        return $response->withJson([
            'status'=> '0',
            'variation'=>$variation,
            'products'=> $products
        ]);
    }

    public function listProducts($request, $response, $args)
    {
        return $response->withJson(Product::getProducts());
    }

    public function buy($request, $response, $args){
        $params = $request->getParams();
        $visitor = $params['user_id'];
        if (!$visitor) return $response->withJson(['status'=> '1','message'=>'vistor ID can not be empty.']);
        $config = $this->session->config;
        if (!$config) return $response->withJson(['status'=> '1','message'=>'project ID does not exist.']);
        if (!$config['project_configuration_json']) return $response->withJson(
            ['status'=> '1','message'=>'Datafile can not be empty.']
        );

        $optimizely_service = new OptimizelyService($config['project_configuration_json']);
        $optimizely_service->track($visitor, $config['event_key']);

        return $response->withJson([
            'status'=> '0'
        ]);
    }

    public function messages($request, $response, $args)
    {
        $logs = $this->session->logs;
        return $response->withJson($logs);
    }

    public function clearMessages($request, $response, $args)
    {
        unset($this->session->logs);
        return $response->withJson([
            'status'=> '0'
        ]);
    }

    function sortProducts($key, $products) {
        $sortArray = array();
        foreach($products as $product){
            foreach($product as $key=>$value){
                if(!isset($sortArray[$key])){
                    $sortArray[$key] = array();
                }
                $sortArray[$key][] = $value;
            }
        }
        return $sortArray;
    }
}


class OptimizelyService {

    public function __construct($datafile) {
        $this->datafile =  $datafile;
        $this->errors = [];
        $this->optimizelyClient = $this->initOpti();
    }

    public function activate($visitor, $experiment_key){
        $variation = $this->optimizelyClient->activate($experiment_key, $visitor);
        return $variation;
    }

    public function track($event_key, $visitor){
        $this->optimizelyClient->track($event_key, $visitor);
    }

    function initOpti(){
        $optimizelyClient = new Optimizely($this->datafile,null, new DemoLogger(Logger::INFO));
        return $optimizelyClient;
    }

}

class Product {
    private static $products = array(
        [
            'id'=> 1,
            'name'=> "Long Sleeve Shirt",
            'color'=> "Baby Blue",
            'category'=> "Shirts",
            'price'=> 54,
            'image_url'=> 'Resources/images/item_1.png'
        ],        [
            'id'=> 2,
            'name'=> "Bo Henry",
            'color'=> "Khaki",
            'category'=> "Shirts",
            'price'=> 37,
            'image_url'=> 'Resources/images/item_2.png'
        ],        [
            'id'=> 3,
            'name'=> "The \"Go\" Bag",
            'color'=> "Forest Green",
            'category'=> "Bags",
            'price'=> 118,
            'image_url'=> 'Resources/images/item_3.png'
        ],        [
            'id'=> 4,
            'name'=> "Springtime",
            'color'=> "Rose",
            'category'=> "Dresses",
            'price'=> 84,
            'image_url'=> 'Resources/images/item_4.png'
        ],        [
            'id'=> 5,
            'name'=> "The Night Out",
            'color'=> "Olive Green",
            'category'=> "Dresses",
            'price'=> 153,
            'image_url'=> 'Resources/images/item_5.png'
        ],        [
            'id'=> 6,
            'name'=> "Dawson Trolley",
            'color'=> "Pine Green",
            'category'=> "Shirts",
            'price'=> 107,
            'image_url'=> 'Resources/images/item_6.png'
        ]
    );

    public static function getProducts($index = false) {
        return $index !== false ? self::$products[$index] : self::$products;
    }
}



class DemoLogger implements LoggerInterface
{
    public function __construct($minLevel = Logger::INFO)
    {
        $formatter = new LineFormatter("[%datetime%] %channel%.%level_name%: %message%\n");
        $streamHandler = new StreamHandler('../logs/app.log', $minLevel);
        $streamHandler->setFormatter($formatter);
        $this->logger = new Logger('Optimizely');
        $this->logger->pushHandler($streamHandler);
        $this->session = new \SlimSession\Helper;
    }
    public function log($logLevel, $logMessage)
    {

        $logArray = array(
            'Timestamp'=>date("Y-m-d H:i:s"),
            'level'=> $logLevel,
            'message'=> $logMessage
        );
        $this->session->merge('logs', ['data' => [$logArray]]);
    }
}