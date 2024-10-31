<?php

namespace Quera;

/**
 * Class for logging events and errors
 *
 * @package     Quera Logger Class
*/
class Logger implements LoggerInterface {

    public const LEVELS = ['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR', 'WARNING', 'NOTICE', 'INFO', 'DEBUG'];

    /**
    * Absolute log file path or log file url
    * @var string
    */
    protected $logPath;

    /**
    * log file
    * @var resource
    */
    protected $logFile;

    /**
     * Log api
     * @var string
     */
    protected $logApi;

    /**
     * Date format
     *
     * @var string
     */
    protected $dateFormat;

    /**
     * Log format
     *
     * @var string
     */
    protected $logFormat;

    /**
     * Threshold
     *
     * @var string
     */
    protected $threshold;

    /**
    * Logger class constructor
    * @param string $logPath - path and filename of log
    * @param array $options - an array of logger writing options
    *
    * @throws LogException
    */
    public function __construct(string $logPath, array $options){
        date_default_timezone_set('Asia/Tehran');

        $this->logPath = $logPath;

        switch ($options['type'] ?? 'file')
        {
            case 'file':
                $this->logFile = fopen($logPath, @$options['append'] ? 'a' : 'w');
                break;

            case 'api':
                $this->logApi = $logPath;
                break;

            default:
                throw new LogException();
        }

        $this->dateFormat = $options['dateFormat'] ?? 'Y-m-d H';
        $this->logFormat = $options['logFormat'] ?? '[{date}]-[{level}]-{message}';
        $this->threshold = $options['threshold'] ?? 'ALL';

        if ($this->threshold != 'ALL' && !in_array($this->threshold, self::LEVELS))
        {
            throw new LogException();
        }
    }

    /**
     * Class destructor
     */
    public function __destruct()
    {
        if ($this->logFile) {
            fclose($this->logFile);
        }
    }

    public function emergency($message, array $context = array())
    {
        $this->log('EMERGENCY', $message, $context);
    }

    public function alert($message, array $context = array())
    {
        $this->log('ALERT', $message, $context);
    }

    public function critical($message, array $context = array())
    {
        $this->log('CRITICAL', $message, $context);
    }

    public function error($message, array $context = array())
    {
        $this->log('ERROR', $message, $context);
    }

    public function warning($message, array $context = array())
    {
        $this->log('WARNING', $message, $context);
    }

    public function notice($message, array $context = array())
    {
        $this->log('NOTICE', $message, $context);
    }

    public function info($message, array $context = array())
    {
        $this->log('INFO', $message, $context);
    }

    public function debug($message, array $context = array())
    {
        $this->log('DEBUG', $message, $context);
    }

    public function log($level, $message, array $context = array())
    {
        if ($this->threshold != 'ALL')
        {
            $minPriority = array_search($this->threshold, self::LEVELS);
            $curPriority = array_search($level, self::LEVELS);

            if ($minPriority < $curPriority)
            {
                return;
            }
        }

        $log = str_replace('{message}', $message, $this->logFormat);

        $log = str_replace(
            ['{date}', '{level}', ...array_map(fn ($x) => "{{$x}}", array_keys($context))],
            [date($this->dateFormat), $level, ...array_values($context)],
            $log,
        );

        if ($this->logFile)
        {
            fwrite($this->logFile, $log . PHP_EOL);
        }
        else
        {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL,$this->logApi);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, ['message' => $log]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_exec($ch);
            curl_close($ch);
        }
    }
}