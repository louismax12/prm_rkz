<?php
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi server database
// Anda bisa menambah alias database lain seperti hrd, askes, keuangan.
define('DB_HOST', '192.168.2.12');
define('DB_PORT', '3306');
define('DB_USER', 'anugrah');
define('DB_PASS', 'anugrah');

$dbConfigs = array(
    'default' => array(
        'host' => DB_HOST,
        'port' => DB_PORT,
        'name' => 'dbold',
        'user' => DB_USER,
        'pass' => DB_PASS
    ),
    'hrd' => array(
        'host' => DB_HOST,
        'port' => DB_PORT,
        'name' => 'hrd',
        'user' => DB_USER,
        'pass' => DB_PASS
    )
);

class Database {
    private $conn;
    private $configs = array(
        'default' => array(
            'host' => DB_HOST,
            'port' => DB_PORT,
            'name' => 'dbold',
            'user' => DB_USER,
            'pass' => DB_PASS
        ),
        'hrd' => array(
            'host' => DB_HOST,
            'port' => DB_PORT,
            'name' => 'hrd',
            'user' => DB_USER,
            'pass' => DB_PASS
        )
    );

    public function __construct() {
        global $dbConfigs;
        if (isset($dbConfigs) && is_array($dbConfigs)) {
            $this->configs = $dbConfigs;
        }
    }

    public function getConnection($dbKey = 'default') {
        $this->conn = null;
        $config = $this->resolveConfig($dbKey);

        $defaultConfig = $this->configs['default'];
        $config = array_merge($defaultConfig, array_filter($config, function($value) {
            return $value !== null && $value !== '';
        }));

        try {
            if (empty($config['host']) || $config['host'] === 'localhost') {
                $config['host'] = '127.0.0.1';
            }

            if (empty($config['name'])) {
                $config['name'] = $defaultConfig['name'];
            }
            if (empty($config['user'])) {
                $config['user'] = $defaultConfig['user'];
            }
            if (!isset($config['pass'])) {
                $config['pass'] = $defaultConfig['pass'];
            }

            $dsn = "mysql:host=" . $config['host'] . ";port=" . $config['port'] . ";dbname=" . $config['name'];
            $this->conn = new PDO($dsn, $config['user'], $config['pass']);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            $debug = sprintf("Connection error (%s:%s db=%s user=%s): %s", $config['host'], $config['port'], $config['name'], $config['user'], $exception->getMessage());
            error_log($debug);
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }

    private function resolveConfig($dbKey) {
        if (is_array($dbKey) && isset($dbKey['name'])) {
            return array(
                'host' => isset($dbKey['host']) ? $dbKey['host'] : null,
                'port' => isset($dbKey['port']) ? $dbKey['port'] : null,
                'name' => $dbKey['name'],
                'user' => isset($dbKey['user']) ? $dbKey['user'] : null,
                'pass' => isset($dbKey['pass']) ? $dbKey['pass'] : null
            );
        }

        if (is_string($dbKey) && isset($this->configs[$dbKey])) {
            return $this->configs[$dbKey];
        }

        return $this->configs['default'];
    }
}
?>
