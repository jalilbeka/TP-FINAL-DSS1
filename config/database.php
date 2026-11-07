<?php
/**
 * Database Connection Configuration
 *
 * Returns a configured PDO instance connected to the DSS University database.
 * Uses singleton pattern to avoid multiple connections per request.
 *
 * @package DSS University
 */

declare(strict_types=1);

class Database
{
    /** @var string Database host */
    private const HOST = '127.0.0.1';

    /** @var string Database name */
    private const DB_NAME = 'dss_university';

    /** @var string Database username */
    private const USERNAME = 'root';

    /** @var string Database password */
    private const PASSWORD = '';

    /** @var string Character set */
    private const CHARSET = 'utf8mb4';

    /** @var PDO|null Singleton PDO instance */
    private static ?PDO $instance = null;

    /**
     * Get the PDO database connection instance.
     *
     * @return PDO Configured PDO instance
     * @throws PDOException If connection fails
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                self::HOST,
                self::DB_NAME,
                self::CHARSET
            );

            self::$instance = new PDO($dsn, self::USERNAME, self::PASSWORD, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '" . self::CHARSET . "'",
            ]);
        }

        return self::$instance;
    }

    /**
     * Prevent instantiation.
     */
    private function __construct()
    {
    }

    /**
     * Prevent cloning.
     */
    private function __clone()
    {
    }
}
