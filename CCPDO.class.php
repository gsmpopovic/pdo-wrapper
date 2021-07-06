<?php

class CCPDO
{
    public $pdo;

    // Total number of queries executed
    public $total_queries = 0;

    // Last query run using the run() method
    public $last_run = [];

    // This will be our most recent statement
    public $statement;

    /**
     * Constructs the class and initializes some variables
     * @param    string  $dsn - DSN connection
     * @param    string  $username - username
     * @param    string  $password - password
     * @optional integer $port        - Port number if needed
     * @return void but will output error if found
     */

    // PDO has a fancy connection method called DSN. It's nothing complicated though
    // instead of one plain and simple list of options,
    // PDO asks you to input different configuration directives in three different places:
    //     database driver, host, db (schema) name and charset,
    //     as well as less frequently used port and unix_socket go into DSN;
    //     username and password go to constructor;
    //     all other options go into options array.
    //     where DSN is a semicolon-delimited string, consists of param=value pairs, that begins from the driver name and a colon:
    // // $dsn should be of the format:
    // $dsn = mysql:host=localhost;dbname=test;port=3306;charset=utf8mb4;
    

    // CONSTRUCTOR
    public function __construct($db, $username = null, $password = null, $host = '127.0.0.1', $port = 3306, $options = [])
    {
        $default_options = [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, ];
        $options = array_replace($default_options, $options);
        $dsn = "mysql:host=$host;dbname=$db;port=$port;charset=utf8mb4";

        try {
            $this->pdo = new \PDO($dsn, $username, $password, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * Runs a SQL query on the database
     * @param string $query - SQL query to run on database
     * @return result identifier on success, false on failure
     */
    public function run($query, $args = null)
    {
        if ($query != '') {
            $this->update_queries();
            $this->last_run[] = [$query, $args];

            if (!$args) {
                $this->statement = $this
                    ->pdo
                    ->query($query);
                return $this
                    ->statement
                    ->fetchAll(PDO::FETCH_ASSOC);
            }

            $this->statement = $this
                ->pdo
                ->prepare($query);

            if (!$this->statement) {
                echo "\nPDO::errorInfo():\n";
                print_r($this
                    ->pdo
                    ->errorInfo());
            }

            $this
                ->statement
                ->execute($args);

            $result = $this
                ->statement
                ->fetchAll(PDO::FETCH_ASSOC);

            return $result;
        } else {
            // Find the error for no query
            echo "\nPDO::errorInfo():\n";
            print_r($this
                ->pdo
                ->errorInfo());
        }
    }

    public function prepare($sql)
    {
        return $this->statement = $this
            ->pdo
            ->prepare($sql);
    }

    public function execute($args)
    {
        return $this
            ->statement
            ->execute($args);
    }

    public function fetch()
    {
        return $this
            ->statement
            ->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Updates the total query count.
     * @return void
     */

    public function update_queries()
    {
        $this->total_queries = $this->total_queries + 1;
    }

    // https://www.php.net/manual/en/pdostatement.rowcount.php
    public function affected_rows()
    {
        $statement = $this->statement;

        // PDOStatement::rowCount() returns the number of rows affected by the last DELETE, INSERT, or UPDATE statement executed by the corresponding PDOStatement object.
        // If the last SQL statement executed by the associated PDOStatement was a SELECT statement,
        // some databases may return the number of rows returned by that statement.
        // However, this behaviour is not guaranteed for all databases and should not be relied on for portable applications.
        if (preg_match("/SELECT/i", $statement->queryString)) {
            return $statement->fetchColumn();
        } else {
            return $statement->rowCount();
        }
    }

    /**
     * https://www.php.net/manual/en/pdostatement.fetch.php
     */
    public function fetch_row()
    {
        if ($this->statement) {

            // Returns an array.
            return $this
                ->statement
                ->fetch(PDO::FETCH_NUM);
        }
    }

    /**
     * https://www.php.net/manual/en/pdostatement.fetch.php
     */
    public function fetch_assoc()
    {
        if ($this->statement) {
            return $this
                ->statement
                ->fetch(PDO::FETCH_ASSOC);
        }
    }

    /**
     * https://www.php.net/manual/en/pdostatement.fetch.php
     */
    public function fetch_array()
    {
        if ($this->statement) {

            // Actually gives us both an array and an associative array.
            return $this
                ->statement
                ->fetch(PDO::FETCH_BOTH);
        }
    }

    /**
     * https://www.php.net/manual/en/pdostatement.closecursor.php
     */
    public function close_cursor()
    {
        // PDOStatement::closeCursor() frees up the connection to the server so that other SQL statements may be issued, but leaves the statement in a state that enables it to be executed again.
        // This method is useful for database drivers that do not support executing a PDOStatement object when a previously executed PDOStatement object still has unfetched rows.
        // If your database driver suffers from this limitation, the problem may manifest itself in an out-of-sequence error.
        if ($this->statement) {
            return $this
                ->statement
                ->closeCursor();
        }
    }

    /**
     * https://www.php.net/manual/en/pdo.getattribute.php
     */
    public function get_client_info()
    {

        // PDO::ATTR_CLIENT_VERSION
        // PDO::ATTR_CONNECTION_STATUS
        // PDO::ATTR_DRIVER_NAME
        if ($this->pdo) {
            return $this
                ->pdo
                ->getAttribute(PDO::ATTR_CLIENT_VERSION);
        }
    }

    /**
     * https://www.php.net/manual/en/pdo.lastinsertid.php
     */
    public function insert_id()
    {

        // Returns the ID of the last inserted row, or the last value from a sequence object, depending on the underlying driver.
        // For example, PDO_PGSQL requires you to specify the name of a sequence object for the name parameter.
        if ($this->pdo) {
            return $this
                ->pdo
                ->lastInsertId();
        }
    }

    /**
     * https://www.php.net/manual/en/pdostatement.columncount.php
     */
    public function num_fields()
    {
        if ($this->statement) {
            return $this
                ->statement
                ->columnCount();
        }
    }

    /**
     * https://www.php.net/manual/en/pdostatement.columncount.php
     */
    public function num_rows()
    {
        if ($this->statement) {
            $rows = $this
                ->statement
                ->fetchAll(PDO::FETCH_ASSOC);
            $num_rows = count($rows);
            return $num_rows;

            // // Get the very last query from our array of recent queries.
            // $recent = array_pop($this->last_query);
            // // If this query had bound arguments,
            // // execute and return count.
            // if($last_args=$recent[1]){
            // 	$this->statement->execute($last_args);
            // 	return count($this->statement->fetchAll());
            // }
            // // Otherwise, execute the query again and count resulting rows.
            // // $recent[0] is the query string of the prepared statement.
            // $rows = $this->pdo->query($recent[0])->fetchAll(PDO::FETCH_ASSOC);
            // $num_rows = count($rows);
            // return $num_rows;
        }
    }

    /** https://www.php.net/manual/en/pdo.transactions.php
     * Will manage transactions
     * @optional string $status - SQL statement to manage transactions
     * @return true on success and if $status not set, false on failure
     */

    // e.g.,
    // try {
    // 	$dbh = new PDO('odbc:SAMPLE', 'db2inst1', 'ibmdb2',
    // 		array(PDO::ATTR_PERSISTENT => true));
    // 	echo "Connected\n";
    //   } catch (Exception $e) {
    // 	die("Unable to connect: " . $e->getMessage());
    //   }
    //   try {
    // 	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // 	$dbh->beginTransaction();
    // 	$dbh->exec("insert into staff (id, first, last) values (23, 'Joe', 'Bloggs')");
    // 	$dbh->exec("insert into salarychange (id, amount, changedate)
    // 		values (23, 50000, NOW())");
    // 	$dbh->commit();
    //   } catch (Exception $e) {
    // 	$dbh->rollBack();
    // 	echo "Failed: " . $e->getMessage();
    //   }
    public function transaction($status = 'BEGIN')
    {
        switch (strtoupper($status)) {
            default:
                return true;
            break;
            case 'START':
            case 'START TRANSACTION':
            case 'BEGIN':
                return $this
                    ->pdo
                    ->beginTransaction();
            break;
            case 'END':
            case 'COMMIT':
                return $this
                    ->pdo
                    ->commit();
            break;
            case 'ROLLBACK':
                return $this
                    ->pdo
                    ->rollBack();
            break;
        }
    }

    /**
     * https://www.php.net/manual/en/pdo.connections.php#114822
     */
    public function close()
    {
        return $this->pdo = null;
    }

    public function error()
    {
        return $this
            ->pdo
            ->errorInfo();
    }

    public function quote($string)
    {
        return $this
            ->pdo
            ->quote($string);
    }
}
