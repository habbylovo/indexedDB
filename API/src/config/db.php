<?php
    class db{
        private $dbHost = '185.201.11.212';
        private $dbUser = 'u860970483_rest';
        private $dbPass = 'nuevo1010$';
        private $dbName = 'u860970483_rest';

        //conexion
        public function conecctionDb(){
            $mysqlConnect = "mysql:host=$this->dbHost;dbname=$this->dbName";
            $dbConnecion = new PDO($mysqlConnect, $this->dbUser, $this->dbPass);
            $dbConnecion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $dbConnecion;
        }
    }