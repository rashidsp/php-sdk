<?php

use Optimizely\Optimizely;
use Optimizely\Logger\DefaultLogger;
use Psr\Log\LoggerInterface;

class OptimizelyService {
    private $logger;

    public function __construct($datafile,LoggerInterface $logger) {
        $this->datafile =  $datafile;
        $this->errors = [];
        $this->optimizely_client = $_SESSION['optimizelyClient'];
        $this->logger = $logger;
    }

    public function instantiate() {
        $this->logger->info('About to find a happy message!');
        $_SESSION['optimizelyClient'] = new Optimizely($this->datafile);
    }

    public function activate($visitor, $experiment_key){
        $variation = $this->optimizely_client->activate($experiment_key, $visitor);
        return $variation;
    }

    public function track($event_key, $visitor){
        $this->optimizely_client ->track($event_key, $visitor);
    }
}