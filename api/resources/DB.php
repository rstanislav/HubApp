<?php
class DB {
	private static $_PDO = null;
	
	final private function __construct() {}
	
	public static function Get() {
		if(self::$_PDO === null) {
			try {
				require_once APP_PATH.'/resources/database.php';
				
				self::$_PDO = new PDO(DB_TYPE.':host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_FOUND_ROWS => TRUE));
				self::$_PDO->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
				self::$_PDO->setAttribute(PDO::ATTR_CASE,               PDO::CASE_NATURAL);
				self::$_PDO->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
				self::$_PDO->setAttribute(PDO::ATTR_ORACLE_NULLS,       PDO::NULL_EMPTY_STRING);
			}
			catch(PDOException $e) {
				throw new RestException(503, 'MySQL');
			}
		}
		
		return self::$_PDO;
	}
}
?>