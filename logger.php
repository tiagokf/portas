<?php
class Logger {
    private $logFile;

    public function __construct($logFile = 'access.log') {
        $this->logFile = $logFile;
    }

    public function logAccess($target, $ports, $results = null) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $timestamp = date('Y-m-d H:i:s');
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        if ($results) {
            $resultStr = json_encode($results);
            $entry = "[$timestamp] IP: $ip | Target: $target | Ports: $ports | Results: $resultStr | UA: $userAgent\n";
        } else {
            $entry = "[$timestamp] IP: $ip | Target: $target | Ports: $ports | UA: $userAgent\n";
        }

        file_put_contents($this->logFile, $entry, FILE_APPEND | LOCK_EX);
    }

    public function getLogs($limit = 50) {
        if (!file_exists($this->logFile)) {
            return [];
        }

        $lines = array_slice(file($this->logFile, FILE_IGNORE_NEW_LINES), -$limit);
        $logs = [];

        foreach ($lines as $line) {
            if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] IP: ([^|]+) \| Target: ([^|]+) \| Ports: ([^|]+)(?: \| Results: (\{.*?\}))? \| UA: (.*)/', $line, $matches)) {
                $logs[] = [
                    'timestamp' => $matches[1],
                    'ip' => trim($matches[2]),
                    'target' => trim($matches[3]),
                    'ports' => trim($matches[4]),
                    'results' => isset($matches[5]) ? json_decode($matches[5], true) : null,
                    'userAgent' => trim($matches[6])
                ];
            }
        }

        return array_reverse($logs);
    }
}
?>