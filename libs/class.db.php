<?php
/**
 * class.db.php
 * 
 * @version 20110607
 * @author Simon Samtleben <web@lemmingzshadow.net>
 * 
 * Description:
 * Version 2 of my mysqli db-class.
 * Hint: This class is a singleton.
 */


class Db
{
	private static $_instance = null;
	private $_mysqli = null;
	private $_statement = null;

	private function __construct($host, $user, $pass, $db)
	{		
		if(self::connect($host, $user, $pass, $db) === false)		
		{
			trigger_error('Could not connect to database.', E_USER_ERROR);
		}		
	}

	private function __clone() {}

	public function __destruct()
	{
		$this->disconnect();
		unset($this->_mysqli, $this->_statement);
	}

	/**
	 * Use instead of constructor to get instance of class.
	 *
	 * @param string $host DB-Server hostname.
	 * @param string $user DB-Server authentication user.
	 * @param string $pass DB-Server authenticatino password.
	 * @param string $db Database to use.
	 * @return object Instance of Db.
	 */
	public static function getInstance($host = null, $user = null, $pass = null, $db = null)
	{
		if(self::$_instance === null)
		{
			self::$_instance = new Db($host, $user, $pass, $db);
		}
		return self::$_instance;
	}
	
	/** 
	 * Connect to a mysql database using mysqli.
	 *	
	 * @param string $host DB-Server hostname.
	 * @param string $user DB-Server authentication user.
	 * @param string $pass DB-Server authenticatino password.
	 * @param string $db Database to use.
	 */
	public function connect($host = null, $user = null, $pass = null, $db = null)
	{		
		$this->_mysqli = mysqli_init();
		$connectResult = $this->_mysqli->real_connect($host, $user, $pass, $db);
		if($connectResult === false)
		{
			return false;
		}
		$this->_mysqli->set_charset("utf8");
		return true;
	}
	
	/**
	 * Close database connection.
	 */
	public function disconnect()
	{
		if($this->_mysqli !== null)
		{
			$this->_mysqli->close();
		}
	}
	
	/**
	 * Replaces placeholders in an sql-statement with according values.
	 * Supported placeholders are:
	 * %d	= Numeric value. Not quoted.
	 * %s	= Quoted string.
	 * %S	= Unquoted string, e.g. 1,2,3 in IN statement (WHERE foo IN(%S))
	 *
	 * @param string $statement The query string. 
	 * @return string Prepared query string.
	 */
	public function prepare($statement = null)
	{		
		if(empty($statement))
		{
			trigger_error('No query given', E_USER_ERROR);
		}
		
		// mask escaped placeholders:
		$statement = str_replace('\%', '{#}', $statement);
		
		// get values and check count:
		$values = func_get_args();
		array_shift($values);		
		if(substr_count($statement, '%s') + substr_count($statement, '%S') + substr_count($statement, '%d') != count($values))
		{
			trigger_error('Passed value-count does not match placeholder-count.', E_USER_ERROR);
		}

		// sanitize query:
		$statement = str_replace("'%s'", '%s', $statement);
		$statement = str_replace('"%s"', '%s', $statement);
		$statement = str_replace("'%d'", '%d', $statement);
		$statement = str_replace('"%d"', '%d', $statement);

		// quote strings (%S is placeholder for unqouted strings):
		$statement = str_replace('%s', "'%s'", $statement);
		$statement = str_replace('%S', '%s', $statement);
		

		// prepare values for use in sql statement:
		foreach(array_keys($values) as $key)
		{
			$values[$key] = $this->_mysqli->real_escape_string($values[$key]);
		}

		// replace placeholders with passed values:
		$statement = vsprintf($statement, $values);

		// unmask:
		$this->_statement = str_replace('{#}', '%', $statement);		

		return $this;
	}
	
	/**
	 * Executes an sql-statement and returns result as array.
	 * 
	 * @param bool $pop Removes first layer in result array if only one result.	 * 
	 * @return array Result of executed sql statement. 
	 */
	public function getResult($pop = true)
	{
		if($this->_executeStatement() === false)
		{
			return false;
		}
		
		$result = array();
		while($row = $this->_result->fetch_array(MYSQLI_ASSOC))
		{
			$result[] = $row;
		}

		if($this->_result->num_rows == 1 && $pop === true)
		{
			$result = $result[0];
		}
		return $result;
	}
	
	/**
	 * Executes an sql-statement.
	 * 
	 * @return bool True if statement could be executed, false if an error occoured.
	 */
	public function execute()
	{
		return $this->_executeStatement();		
	}
	
	/**
	 * Returns number of result rows.
	 *
	 * @return int Number of results.
	 */
	public function getResultCount()
	{
		return $this->_result->num_rows;
	}

	/**
	 * Returns mysqli error message.
	 *
	 * @return string Error message.
	 */
	public function getError()
	{
		return $this->_mysqli->error;
	}
	
	/**
	 * Returns id of last insert operation.
	 *
	 * @return int Id of last insert operation.
	 */
	public function getInsertId()
	{
		return $this->_mysqli->insert_id;
	}
	
	public function getAffectedRows()
	{
		return $this->_mysqli->affected_rows;
	}


	
	/**
	 * Executes an mysql-statement.
	 * 
	 * @return type bool True is statement could be executed, false otherwise.
	 */
	private function _executeStatement()
	{
		if(empty($this->_statement))
		{
			trigger_error('No query given.', E_USER_ERROR);
		}		
		$this->_result = $this->_mysqli->query($this->_statement);
		$this->_statement = null;
		
		return ($this->_result === false) ? false : true;		
	}
}