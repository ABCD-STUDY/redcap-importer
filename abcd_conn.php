<?php
  class Conn{
    private $host;
    private $db_name;
    private $db_username;
    private $db_password;
    public $dbh;
    /**
     *
     * Set variables 
     *
     */
     public function __construct( $db_host, $db_name, $db_username, $db_password ){

            $this->host = $db_host;
            $this->db_name = $db_name;
            $this->db_username = $db_username;
            $this->db_password = $db_password;
        }

        public function connect(){
            try{                
                $opt = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
                $this->dbh = new PDO("mysql:host={$this->host}; db_name ={$this->db_name}", 
                           $this->db_username, $this->db_password, $opt);
            }
            catch(PDOException $e){
                $error = "Error: ".$e->getMessage().'<br />';
                echo $error;
                return false;
            }
            return true;
        }
  }
    require_once('abcd_etc.php');
    $db_host = "localhost";
    $db_name = ABCD_D;
    $db_username = ABCD_U;
    $db_password = ABCD_P;

    $db = new Conn($db_host, $db_name, $db_username, $db_password);

    $result = $db->connect();

    if(!$result){
        echo "DB is not Connected <br />";      
    } 
?>
