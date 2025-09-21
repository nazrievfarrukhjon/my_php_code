<?php

namespace App\Log;

class Logger implements LoggerInterface
{
    private static ?Logger $instance = null;

    private function __construct() {}
    public static function getInstance(): Logger {
        if (self::$instance === null) {
            self::$instance = new Logger();
        }
        return self::$instance;
    }

    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, $message, array $context = []): void
    {
        // Replace placeholders in the message with context values
        $message = $this->interpolate($message, $context);

        // Format log line
        $time = date('Y-m-d H:i:s');
        $formatted = "[{$time}] {$level}: {$message} " . json_encode($context) . "\n";

        // Send to PHP's error log
        error_log($formatted, 3, ROOT_DIR . '/logs/app.log');

    }

    private function interpolate(string $message, array $context): string
    {
        // Interpolates context values into message placeholders
        $replace = [];
        foreach ($context as $key => $val) {
            // Check that the value can be cast to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        return strtr($message, $replace);
    }
}
