<?php

class UosOrcid 
{
	private $pinumber;
	private $orcid;
	private $fromDb;

	// construct (doesn't mean it exists in the database)
	public function __construct( $pinumber, $orcid, $fromDb=false )
	{
		$this->orcid = $orcid;			
		$this->pinumber = $pinumber;			
		$this->fromDb = $fromDb;			
	}

	function pinumber() { return $this->pinumber; }
	function orcid() { return $this->orcid; }

	#########################################
	# db read functions
	#########################################

	// return false or a valid object
	public static function fromPinumber($pinumber)
	{
		$db = UosOrcidDB::db();
		$sql = "SELECT pinumber, orcid FROM orcid_map WHERE "
		     . "pinumber='".$db->real_escape_string( $pinumber )."'";
		$result = $db->query( $sql );
		$row = $result->fetch_assoc();
		$result->free();
		if( !$row ) { return false; }
		return new UosOrcid( $row["pinumber"], $row["orcid"], true );
	}

	// returns false or a valid object
	public static function fromOrcid($orcid)
	{
		$db = UosOrcidDB::db();
		$sql = "SELECT pinumber, orcid FROM orcid_map WHERE "
		     . "orcid='".$db->real_escape_string( $orcid )."'";
		$result = $db->query( $sql );
		$row = $result->fetch_assoc();
		$result->free();
		if( !$row ) { return false; }
		return new UosOrcid( $row["pinumber"], $row["orcid"], true );
	}

	// returns an associative array of all records
	// may need rethinking if this ever gets big
	// but it's unlikely to ever be more than 50K records
	// so what the hell?
	// keys of array are pinumber, values are objects
	public static function allRecords()
	{
		$db = UosOrcidDB::db();
		$records = array();
		$sql = "SELECT pinumber, orcid FROM orcid_map";
		$result = $db->query( $sql );
		while( $row = $result->fetch_assoc() )
		{
			$records[ $row["pinumber"] ] = new UosOrcid( $row["pinumber"], $row["orcid"], true );
		}
		$result->free();
		return $records;
	}

	#########################################
	# db write functions
	#########################################

	// return true if the write worked
	public function write()
	{
		$db = UosOrcidDB::db();
		$sql = "REPLACE INTO orcid_map ( pinumber , orcid ) VALUES ("
		     . "'".$db->real_escape_string( $this->pinumber )."',"
		     . "'".$db->real_escape_string( $this->orcid )."' )";
		$result = $db->query( $sql );
		return $result;
	}

	// return true if the remove worked
	public function remove()
	{
		$db = UosOrcidDB::db();
		$sql = "DELETE FROM orcid_map WHERE "
		     . "pinumber='".$db->real_escape_string( $this->pinumber )."'";
		$result = $db->query( $sql );
		if( $result ) 
		{
			$this->fromDb = false;
		}
		return $result;
	}
		
}

class UosOrcidDB
{
	private static $db;
	
	public static function db()
	{
		$f3 = Base::instance();

		if( !@UosOrcidDB::$db )
		{
			UosOrcidDB::$db = new mysqli(
				$f3->get('db_host'),
				$f3->get('db_user'),
				$f3->get('db_password'),
				$f3->get('db_name') );
		}

		return UosOrcidDB::$db;
	}

}
