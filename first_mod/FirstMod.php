<?php

namespace modules\first_mod;                        //Make sure namespace is same structure with parent directory

use \classes\Auth as Auth;                          //For authentication internal user
use \classes\JSON as JSON;                          //For handling JSON in better way
use \classes\CustomHandlers as CustomHandlers;      //To get default response message
use PDO;                                            //To connect with database

	/**
     * This is my First Modules class
     *
     * @package    reSlim-modules
     * @author     M ABD AZIZ ALFIAN <github.com/aalfiann>
     * @copyright  Copyright (c) 2018 M ABD AZIZ ALFIAN
     * @license    https://github.com/aalfiann/reSlim/blob/master/license.md  MIT License
     */
    class FirstMod {
        // modules information var
        protected $information = [
            'module' => [
                'name' => 'FirstMod',
                'uri' => 'https://github.com/aalfiann/reSlim-modules/first_mod',
                'description' => 'This is my First Modules',
                'version' => '1.0'
            ],
            'author' => [
                'name' => 'M ABD AZIZ ALFIAN',
                'uri' => 'https://github.com/aalfiann'
            ]
        ];

        // database var
        protected $db;

        //master var
		var $username,$token;
        
        //construct database object
        function __construct($db=null) {
			if (!empty($db)) {
    	        $this->db = $db;
        	}
        }

        //Get modules information (no database required)
        public function viewInfo(){
            return JSON::encode($this->information,true);
        }

        //Determine token validation (required database)
        public function checkToken(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
                $data = [
                    'status' => 'success',
                    'code' => 'RS304',
                    'message' => CustomHandlers::getreSlimMessage('RS304')
                ];
            } else {
                $data = [
	    			'status' => 'error',
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401')
				];
            }
            return JSON::safeEncode($data,true);
	        $this->db= null;
        }

    }