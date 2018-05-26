<?php
namespace modules\crud_mod;                         //Make sure namespace is same structure with parent directory

use \classes\Auth as Auth;                          //For authentication internal user
use \classes\JSON as JSON;                          //For handling JSON in better way
use \classes\CustomHandlers as CustomHandlers;      //To get default response message
use \classes\Validation as Validation;              //To validate the string
use PDO;                                            //To connect with database

	/**
     * Example to create crud module in reSlim
     *
     * @package    modules/crud_mod
     * @author     M ABD AZIZ ALFIAN <github.com/aalfiann>
     * @copyright  Copyright (c) 2018 M ABD AZIZ ALFIAN
     * @license    https://github.com/aalfiann/reSlim-modules/tree/master/crud_mod/LICENSE.md  MIT License
     */
    class CrudMod {
        // modules information var
        protected $information = [
            'package' => [
                'name' => 'CrudMod',
                'uri' => 'https://github.com/aalfiann/reSlim-modules/tree/master/crud_mod',
                'description' => 'Example to create crud module in reSlim',
                'version' => '1.0',
                'require' => [
                    'reSlim' => '1.9.0'
                ],
                'license' => [
                    'type' => 'MIT',
                    'uri' => 'https://github.com/aalfiann/reSlim-modules/tree/master/crud_mod/LICENSE.md'
                ],
                'author' => [
                    'name' => 'M ABD AZIZ ALFIAN',
                    'uri' => 'https://github.com/aalfiann'
                ],
            ]
        ];

        // database var
		protected $db;
		
		//base var
        protected $basepath,$baseurl;

        //master var
        var $username,$token;

        //data var
        var $id,$fullname,$address,$telp,$email,$website;

        //search var
        var $search;
        
        //pagination var
        var $page,$itemsPerPage;
        
        //construct database object
        function __construct($db=null,$baseurl=null) {
			if (!empty($db)) $this->db = $db;
            $this->baseurl = (($this->isHttps())?'https://':'http://').$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
            $this->basepath = $_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF']);
        }
        
        //Detect scheme host
        function isHttps() {
            $whitelist = array(
                '127.0.0.1',
                '::1'
            );
            
            if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
                if (!empty($_SERVER['HTTP_CF_VISITOR'])){
                    return isset($_SERVER['HTTPS']) ||
                    ($visitor = json_decode($_SERVER['HTTP_CF_VISITOR'])) &&
                    $visitor->scheme == 'https';
                } else {
                    return isset($_SERVER['HTTPS']);
                }
            } else {
                return 0;
            }            
        }

        //Get modules information
        public function viewInfo(){
            return JSON::encode($this->information,true);
        }

        /**
         * Installation (Build database table) 
         */
        public function install(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
				$role = Auth::getRoleID($this->db,$this->token);
				if ($role == 1){
					try {
						$this->db->beginTransaction();
						$sql = file_get_contents(dirname(__FILE__).'/crud_mod.sql');
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
         * Uninstall (Remove database table) 
         */
        public function uninstall(){
            if (Auth::validToken($this->db,$this->token,$this->username)){
				$role = Auth::getRoleID($this->db,$this->token);
				if ($role == 1){
					try {
						$this->db->beginTransaction();
						$sql = "DROP TABLE IF EXISTS crud_mod;";
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

        //CRUD===========================================


        public function create() {
            if (Auth::validToken($this->db,$this->token,$this->username)){
    		    try {
    				$this->db->beginTransaction();
	    			$sql = "INSERT INTO crud_mod (Fullname,Address,Telp,Email,Website) 
		    			VALUES (:fullname,:address,:telp,:email,:website);";
					$stmt = $this->db->prepare($sql);
					$stmt->bindParam(':fullname', $this->fullname, PDO::PARAM_STR);
					$stmt->bindParam(':address', $this->address, PDO::PARAM_STR);
					$stmt->bindParam(':telp', $this->telp, PDO::PARAM_STR);
					$stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
                    $stmt->bindParam(':website', $this->website, PDO::PARAM_STR);
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
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401')
				];
            }

			return JSON::encode($data,true);
			$this->db = null;
        }

        public function update() {
            if (Auth::validToken($this->db,$this->token,$this->username)){
    		    try {
    				$this->db->beginTransaction();
	    			$sql = "UPDATE crud_mod 
                        SET Fullname=:fullname,Address=:address,Telp=:telp,Email=:email,Website=:website
                        WHERE ID=:id;";
					$stmt = $this->db->prepare($sql);
					$stmt->bindParam(':fullname', $this->fullname, PDO::PARAM_STR);
					$stmt->bindParam(':address', $this->address, PDO::PARAM_STR);
					$stmt->bindParam(':telp', $this->telp, PDO::PARAM_STR);
					$stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
                    $stmt->bindParam(':website', $this->website, PDO::PARAM_STR);
                    $stmt->bindParam(':id', $this->id, PDO::PARAM_STR);
                    if ($stmt->execute()) {
						$data = [
							'status' => 'success',
							'code' => 'RS103',
							'message' => CustomHandlers::getreSlimMessage('RS103')
						];	
					} else {
    					$data = [
					    	'status' => 'error',
				    		'code' => 'RS203',
			    			'message' => CustomHandlers::getreSlimMessage('RS203')
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
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401')
				];
            }

			return JSON::encode($data,true);
			$this->db = null;
        }

        public function delete() {
            if (Auth::validToken($this->db,$this->token,$this->username)){
    		    try {
    				$this->db->beginTransaction();
	    			$sql = "DELETE FROM crud_mod WHERE ID=:id;";
					$stmt = $this->db->prepare($sql);
					$stmt->bindParam(':id', $this->id, PDO::PARAM_STR);
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
					'code' => 'RS401',
        	    	'message' => CustomHandlers::getreSlimMessage('RS401')
				];
            }

			return JSON::encode($data,true);
			$this->db = null;
        }

        public function read() {
            if (Auth::validToken($this->db,$this->token,$this->username)){
				$sql = "SELECT a.ID,a.Fullname,a.Address,a.Telp,a.Email,a.Website
						FROM crud_mod a
						WHERE a.ID = :id LIMIT 1;";
				
				$stmt = $this->db->prepare($sql);		
				$stmt->bindParam(':id', $this->id, PDO::PARAM_STR);

				if ($stmt->execute()) {	
    	    	    if ($stmt->rowCount() > 0){
                        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
						$data = [
			   	            'result' => $results, 
    	    		        'status' => 'success', 
			           	    'code' => 'RS501',
        		        	'message' => CustomHandlers::getreSlimMessage('RS501')
						];
			        } else {
        			    $data = [
            		    	'status' => 'error',
		        		    'code' => 'RS601',
        		    	    'message' => CustomHandlers::getreSlimMessage('RS601')
						];
	    	        }          	   	
				} else {
					$data = [
    	    			'status' => 'error',
						'code' => 'RS202',
	        		    'message' => CustomHandlers::getreSlimMessage('RS202')
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
	        $this->db= null;
        }

        public function index() {
            if (Auth::validToken($this->db,$this->token)){
				$search = "%$this->search%";
				//count total row
				$sqlcountrow = "SELECT count(a.ID) as TotalRow 
					from crud_mod a
					where a.ID like :search
                    or a.Fullname like :search
					order by a.Fullname asc;";
				$stmt = $this->db->prepare($sqlcountrow);		
				$stmt->bindParam(':search', $search, PDO::PARAM_STR);
				
				if ($stmt->execute()) {	
    	    		if ($stmt->rowCount() > 0){
						$single = $stmt->fetch();
						
						// Paginate won't work if page and items per page is negative.
						// So make sure that page and items per page is always return minimum zero number.
						$newpage = Validation::integerOnly($this->page);
						$newitemsperpage = Validation::integerOnly($this->itemsPerPage);
						$limits = (((($newpage-1)*$newitemsperpage) <= 0)?0:(($newpage-1)*$newitemsperpage));
						$offsets = (($newitemsperpage <= 0)?0:$newitemsperpage);

						// Query Data
						$sql = "SELECT a.ID,a.Fullname,a.Address,a.Telp,a.Email,a.Website 
							from crud_mod a
							where a.ID like :search
                            or a.Fullname like :search
							order by a.Fullname asc LIMIT :limpage , :offpage;";
						$stmt2 = $this->db->prepare($sql);
						$stmt2->bindParam(':search', $search, PDO::PARAM_STR);
						$stmt2->bindValue(':limpage', (INT) $limits, PDO::PARAM_INT);
						$stmt2->bindValue(':offpage', (INT) $offsets, PDO::PARAM_INT);
						
						if ($stmt2->execute()){
							$pagination = new \classes\Pagination();
							$pagination->totalRow = $single['TotalRow'];
							$pagination->page = $this->page;
							$pagination->itemsPerPage = $this->itemsPerPage;
							$pagination->fetchAllAssoc = $stmt2->fetchAll(PDO::FETCH_ASSOC);
							$data = $pagination->toDataArray();
						} else {
							$data = [
        	    	    		'status' => 'error',
		        		    	'code' => 'RS202',
	    			    	    'message' => CustomHandlers::getreSlimMessage('RS202')
							];	
						}			
				    } else {
    	    			$data = [
        	    			'status' => 'error',
		    	    		'code' => 'RS601',
        			    	'message' => CustomHandlers::getreSlimMessage('RS601')
						];
		    	    }          	   	
				} else {
					$data = [
    	    			'status' => 'error',
						'code' => 'RS202',
	        		    'message' => CustomHandlers::getreSlimMessage('RS202')
					];
				}
				
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