<?php
namespace Zf\Ext;

use Doctrine\DBAL\Logging\DebugStack;
use Laminas\Log\Formatter\Simple;
use Laminas\Log\Logger;
use Laminas\Log\Writer\Stream;

class SqlLogger extends DebugStack
{
    /**
     * Logger
     * @var Logger
     */
    protected $logger;
    
    public function __construct(Logger $logger, array $configs, $name = null)
    {
        parent::__construct();
        $this->logger = $logger;
        
        $savePath = $configs['path'] ?? '/var/log';
        $rotatePattern = $configs['rotate_pattern'] ?? 'Y_m_d';
        $dailyLogFileName = 'doctrine_queries-' . date($rotatePattern) . '.log';
        
        $writer = new Stream("{$savePath}/{$dailyLogFileName}");
        
        $writer->setFormatter(
            new Simple('172.17.0.1 - - [%message%]')
        );
        
        $this->logger->addWriter($writer);
        
    }


    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        parent::stopQuery();
        
        $retrieveData = $this->queries[$this->currentQuery];
        
        if (!empty($retrieveData['params'])){
            foreach ($retrieveData['params'] as $key => $val) {
                if (is_array($val)) $retrieveData['params'][$key] = '"' . implode('","', $val) . '"';
            }
        }else $retrieveData['params'] = [];
        
        $sql = str_replace(['%', '?'], ['%s', '_'], $retrieveData['sql']);
        
        if (
            preg_match_all('/(\:([\w]+)|(\%\s))/mi', $sql, $matchs) 
            && isset($matchs[1])
        ){
            $matchs[1] = array_unique($matchs[1]);
            
            $total = min([count($matchs[1]), count($retrieveData['params'])]);
            
            $sqlLen = strlen(str_replace(
                $matchs[1], 
                array_combine(
                    array_slice($matchs[1], 0, $total),
                    array_slice($retrieveData['params'], 0, $total)
                ), 
                $sql
            ));
        }else{
            try {
                $sqlLen = strlen(
                    vsprintf($sql, $retrieveData['params'] ?? [])
                );
            }catch (\Throwable $e){
                $sqlLen = strlen($sql . strlen(
                    json_encode($retrieveData['params'] ?? [])
                ));
            }
        }
        
        $message = date('d/M/Y:H:i:s O') . '] "GET / HTTP/1.1" 200 '.$sqlLen.' "'.
            preg_replace('/[\n\r\t]|(\s{3})/m', '', $retrieveData['sql'])
            .'" ""';
        $this->logger->info($message);
    }
}