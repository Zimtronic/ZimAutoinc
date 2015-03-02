<?php

namespace Zim\AutoincBundle\AutoIncrement;

use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Symfony\Bridge\Monolog\Logger;

class Generator
{
    private $logger;
    private $manager;
    private $collection;
    private $counters;
    private $config;
    
    public function __construct($config, Logger $logger, ManagerRegistry $manager)
    {
        $this->logger = $logger;
        $this->manager = $manager;
        $this->collection = $config['collection'];
        $this->counters = $config['counters'];
        
    }
    
    public function generate($counter)
    {
        if(!in_array($counter, $this->counters))
            throw new \Exception('Requested counter name value is not configured');
        
        $dbname = $this->manager->getConnection()->getConfiguration()->getDefaultDB();
        $coll = $this->manager->getConnection()->getMongo()->selectCollection($dbname, $this->collection);

        $action = function () use ($coll, $counter){
            
            return $coll->findAndModify(
                                        ['_id' => $counter],
                                        ['$inc' => ['count' => 1]],
                                        null,
                                        [
                                            'upsert' => true,
                                            'new'    => true
                                        ]
                                    )['count'];
        };
        
        return $this->retry($action, $this->manager
                                   ->getConnection()
                                   ->getConfiguration()
                                   ->getRetryConnect()
                           );
    }
    
    
    protected function retry(\Closure $retry, $numRetries)
    {
        if ($numRetries < 1) {
            return $retry();
        }
        $firstException = null;
        for ($i = 0; $i <= $numRetries; $i++) {
            try {
                return $retry();
            } catch (\MongoException $e) {
                if ($firstException === null) {
                    $firstException = $e;
                }
                if ($i === $numRetries) {
                    throw $firstException;
                }
            }
        }
    }
    
}
