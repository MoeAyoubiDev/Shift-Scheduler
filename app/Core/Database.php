<?php
declare(strict_types=1);

class Database
{
    private static ?PDO $instance = null;
    
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../../config/database.php';
            
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $config['host'],
                $config['port'],
                $config['name']
            );
            
            self::$instance = new PDO(
                $dsn,
                $config['user'],
                $config['pass'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }
        
        return self::$instance;
    }
    
    public static function callProcedure(string $procedureName, array $params = []): array
    {
        $db = self::getInstance();
        $placeholders = [];
        
        for ($i = 0; $i < count($params); $i++) {
            $placeholders[] = '?';
        }
        
        $sql = "CALL {$procedureName}(" . implode(', ', $placeholders) . ")";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        $results = [];
        do {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($result)) {
                $results[] = $result;
            }
        } while ($stmt->nextRowset());
        
        return $results[0] ?? [];
    }
    
    public static function callProcedureWithOut(string $procedureName, array $inParams = [], array $outParams = []): array
    {
        $db = self::getInstance();
        $allParams = array_merge($inParams, $outParams);
        $placeholders = [];
        
        foreach ($allParams as $param) {
            $placeholders[] = '?';
        }
        
        $sql = "CALL {$procedureName}(" . implode(', ', $placeholders) . ")";
        $stmt = $db->prepare($sql);
        
        // Bind OUT parameters
        $outIndex = count($inParams);
        foreach ($outParams as $index => $outParam) {
            $stmt->bindParam($outIndex + $index + 1, ${'out' . $index}, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT, 4000);
        }
        
        $stmt->execute($inParams);
        
        $results = [];
        do {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($result)) {
                $results[] = $result;
            }
        } while ($stmt->nextRowset());
        
        // Collect OUT parameter values
        $outValues = [];
        foreach ($outParams as $index => $outParam) {
            $outValues[$outParam] = ${'out' . $index} ?? null;
        }
        
        return [
            'results' => $results[0] ?? [],
            'out' => $outValues
        ];
    }
}

