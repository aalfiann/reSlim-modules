<?php

namespace modules\enterprise;                       //Make sure namespace is same structure with parent directory

use \classes\Auth as Auth;                          //For authentication internal user
use \classes\JSON as JSON;                          //For handling JSON in better way
use \classes\CustomHandlers as CustomHandlers;      //To get default response message
use PDO;                                            //To connect with database

	/**
     * Enterprise management system
     *
     * @package    modules/enterprise
     * @author     M ABD AZIZ ALFIAN <github.com/aalfiann>
     * @copyright  Copyright (c) 2018 M ABD AZIZ ALFIAN
     * @license    https://github.com/aalfiann/reSlim-modules/tree/master/enterprise/LICENSE.md  MIT License
     */
    class Enterprise {

        // database var
		protected $db;
		
		//base var
        protected $basepath,$baseurl,$basemod;

        //master var
		var $username,$token;
        
        //construct database object
        function __construct($db=null) {
			if (!empty($db)) $this->db = $db;
            $this->baseurl = (($this->isHttps())?'https://':'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
            $this->basepath = $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF']);
			$this->basemod = dirname(__FILE__);
        }

        //Get modules information
        public function viewInfo(){
            return file_get_contents($this->basemod.'/package.json');
        }

        /**
         * Build database table 
         */
        public function install(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
				$role = Auth::getRoleID($this->db,$this->token);
				if ($role == 1){
					try {
						$this->db->beginTransaction();
						$sql = file_get_contents(dirname(__FILE__).'/enterprise.sql');
						$stmt = $this->db->prepare($sql);
						if ($stmt->execute()) {
							$data = [
								'status' => 'success',
								'code' => 'RS101',
								'message' => CustomHandlers::getreSlimMessage('RS101')
							];	
						} else {
							$data = [
								'status' => 'error',
								'code' => 'RS201',
								'message' => CustomHandlers::getreSlimMessage('RS201')
							];
						}
						$this->db->commit();
					} catch (PDOException $e) {
						$data = [
							'status' => 'error',
							'code' => $e->getCode(),
							'message' => $e->getMessage()
						];
						$this->db->rollBack();
					}
				} else {
					$data = [
						'status' => 'error',
						'code' => 'RS404',
						'message' => CustomHandlers::getreSlimMessage('RS404')
					];
				}
            } else {
                $data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401')
				];
            }

			return JSON::encode($data,true);
			$this->db = null;
        }

        /**
         * Remove database table 
         */
        public function uninstall(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
				$role = Auth::getRoleID($this->db,$this->token);
				if ($role == 1){
					try {
						$this->db->beginTransaction();
						$sql = "DROP TABLE IF EXISTS sys_company;DROP TABLE IF EXISTS sys_user;";
						$stmt = $this->db->prepare($sql);
						if ($stmt->execute()) {
							$data = [
								'status' => 'success',
								'code' => 'RS104',
								'message' => CustomHandlers::getreSlimMessage('RS104')
							];	
						} else {
							$data = [
								'status' => 'error',
								'code' => 'RS204',
								'message' => CustomHandlers::getreSlimMessage('RS204')
							];
						}
						$this->db->commit();
					} catch (PDOException $e) {
						$data = [
							'status' => 'error',
							'code' => $e->getCode(),
							'message' => $e->getMessage()
						];
						$this->db->rollBack();
					}
				} else {
					$data = [
						'status' => 'error',
						'code' => 'RS404',
						'message' => CustomHandlers::getreSlimMessage('RS404')
					];
				}
            } else {
                $data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401')
				];
            }

			return JSON::encode($data,true);
			$this->db = null;
        }
    }