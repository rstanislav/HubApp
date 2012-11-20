<?php
/**
 * //@protected
**/
class Log {
	private $PDO;
	
	function __construct() {
		$this->PDO = DB::Get();
	}
	
	/**
	 * @url GET /
	 * @url GET /days/:Days
	 * @url GET /lines/:Lines
	**/
	function LogAll($Days = 1, $Lines = 0) {
		if(!is_numeric($Days) || !is_numeric($Lines)) {
			throw new RestException(412, 'Number of Days or Lines must be a numeric value');
		}
		else {
			if($Days) {
				$LogQuery = 'SELECT
				             	*
				             FROM
				             	Log
				             WHERE
				             	Date > '.strtotime('-'.$Days.' days').'
				             ORDER BY
				             	ID
				             DESC';
			}
			else {
				$LogQuery = 'SELECT
				             	*
							 FROM
							 	Log
							 ORDER BY
							 	ID
							 DESC
							 LIMIT '.$Lines;
			}
		}
		
		try {
			$LogPrep = $this->PDO->prepare($LogQuery);
			$LogPrep->execute();
			
			if($LogPrep->rowCount()) {
				return $LogPrep->fetchAll();
			}
			else {
				throw new RestException(404, 'Did not find anything in the log matching your criteria');
			}
		}
		catch(PDOException $e) {
			throw new RestException(400, 'MySQL: '.$e->getMessage());
		}
	}
}
?>