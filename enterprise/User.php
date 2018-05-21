<?php
/**
 * This class is a part of reSlim project
 * @author M ABD AZIZ ALFIAN <github.com/aalfiann>
 *
 * Don't remove this class unless You know what to do
 *
 */
namespace modules\enterprise;
use \classes\Auth as Auth;
use \classes\JSON as JSON;
use \classes\Validation as Validation;
use \classes\CustomHandlers as CustomHandlers;
use PDO;
	/**
     * A class for user management
     *
     * @package    Enterprise User
     * @author     M ABD AZIZ ALFIAN <github.com/aalfiann>
     * @copyright  Copyright (c) 2018 M ABD AZIZ ALFIAN
     * @license    https://github.com/aalfiann/reSlim/blob/master/license.md  MIT License
     */
	class User {
        // model data user
		var $username,$branchid,$statusid,$adminname;
		
		// for pagination
		var $page,$itemsPerPage;

		// for search
		var $search;

		protected $db;
        
        function __construct($db=null) {
			if (!empty($db)) 
	        {
    	        $this->db = $db;
        	}
		}
		
		/**
		 * Inserting into database to register user
		 * @return result process in json encoded data
		 */
		private function doRegister(){
			
			$newusername = strtolower($this->username);
			$newadminname = strtolower($this->adminname);
			$newbranchid = strtolower($this->branchid);
			
			try {
				$this->db->beginTransaction();
				$sql = "INSERT INTO sys_user (Username,BranchID,StatusID,Created_by) 
					VALUES (:username,:branchid,'1',:adminname);";
					$stmt = $this->db->prepare($sql);
					$stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
					$stmt->bindParam(':adminname', $newadminname, PDO::PARAM_STR);
					$stmt->bindParam(':branchid', $newbranchid, PDO::PARAM_STR);
					if ($stmt->execute()) {
						$data = [
							'status' => 'success',
							'code' => 'RS101',
							'message' => CustomHandlers::getreSlimMessage('RS101')
						];	
					} else {
						$data = [
							'status' => 'error',
							'code' => 'RS901',
							'message' => CustomHandlers::getreSlimMessage('RS901')
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
			return $data;
			$this->db = null;
		}

		/**
		 * Determine if user is already registered or not
		 * @return boolean true / false
		 */
		private function isRegistered(){
			$r = false;
			$newusername = strtolower($this->username);
			if (Auth::isKeyCached('user-'.$newusername.'-registered',86400)){
                $r = true;
            } else {
				$sql = "SELECT a.Username
					FROM sys_user a 
					WHERE a.Username = :username;";
				$stmt = $this->db->prepare($sql);
				$stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
				if ($stmt->execute()) {	
            		if ($stmt->rowCount() > 0){
						$r = true;
						Auth::writeCache('user-'.$newusername.'-registered');
	    	        }          	   	
				}
			} 		
			return $r;
			$this->db = null;
		}

		/**
		 * Determine if user is active or not
         * 
		 * @return boolean true / false
		 */
		private static function isUserActive(){
            $r = false;
            $newusername = strtolower($this->username);
            if (Auth::isKeyCached('user-'.$newusername.'-active',86400)){
                $r = true;
            } else {
                $sql = "SELECT a.Username
			    	FROM sys_user a 
				    WHERE a.Username = :username AND a.StatusID='1';";
    			$stmt = $this->db->prepare($sql);
	    		$stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
		    	if ($stmt->execute()) {	
                	if ($stmt->rowCount() > 0){
                        $r = true;
                        Auth::writeCache('user-'.$newusername.'-active');
        	        }          	   	
	    		}
            } 		
			return $r;
			$db = null;
		}

		/**
		 * Determine if user is already registered in main app or not
		 * @return boolean true / false
		 */
		private function isMainUserExist(){
			$r = false;
			$newusername = strtolower($this->username);
			if (Auth::isKeyCached('user-'.$newusername.'-exists',86400)){
                $r = true;
            } else {
				$sql = "SELECT a.Username
					FROM user_data a 
					WHERE a.Username = :username;";
				$stmt = $this->db->prepare($sql);
				$stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
				if ($stmt->execute()) {	
            		if ($stmt->rowCount() > 0){
						$r = true;
						Auth::writeCache('user-'.$newusername.'-exists');
	    	        }          	   	
				}
			} 		
			return $r;
			$this->db = null;
		}

		/** 
         * Get information user branchid by username
         *
         * @return string BranchID
         */
        private function getBranchID(){
			$roles = "";
			$newusername = strtolower($this->username);
            if (Auth::isKeyCached('user-'.$newusername.'-branchid',86400)){
                $data = json_decode(Auth::loadCache('user-'.$newusername.'-branchid'));
                if (!empty($data)){
                    $roles = $data->Role;
                }
            } else {
                $sql = "SELECT a.BranchID FROM sys_user a WHERE a.Username =:username limit 1;";
	    		$stmt = $this->db->prepare($sql);
		    	$stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
			    if ($stmt->execute()){
				    if ($stmt->rowCount() > 0){
    					$single = $stmt->fetch();
                        $roles = $single['BranchID'];
                        Auth::writeCache('user-'.$newusername.'-branchid',$roles);
		    		}
			    }
            }
			return $roles;
			$this->db = null;
        }

		/** 
		 * Regiter new user
		 * @return result process in json encoded data
		 */
		public function register(){
			if ( preg_match('/[A-Za-z0-9]+/',$this->username) == false ){
				$data = [
					'status' => 'error',
					'code' => 'RS804',
					'message' => CustomHandlers::getreSlimMessage('RS804')
				];
			} else {
				if ($this->isRegistered() == false){
					if ($this->isMainUserExist()){
						$data = $this->doRegister();
					} else {
						$data = [
							'status' => 'error',
							'code' => 'RS901',
							'message' => CustomHandlers::getreSlimMessage('RS901')
						];
					}
				} else {
					$data = [
						'status' => 'error',
						'code' => 'RS902',
						'message' => CustomHandlers::getreSlimMessage('RS902')
					];
				}
			}
			
			return JSON::encode($data,true);
		}

		/** 
         * Update user
         *
         * @return json encoded data
         */
		public function update(){
			if (Auth::validToken($this->db,$this->token,$this->adminname)){
                $roles = Auth::getRoleID($this->db,$this->token);
                if ($roles < 3 || $roles == '6'){
					$newusername = strtolower($this->username);
					$newadminname = strtolower($this->adminname);
                    $newbranchid = strtolower($this->branchid);
                    $newstatusid = Validation::integerOnly($this->statusid);
                    
		    		try{
                        $this->db->beginTransaction();
    
                        $sql = "UPDATE sys_user a SET a.BranchID=:branchid,a.StatusID=:statusid,a.Updated_by=:adminname
                            WHERE a.Username = :username;";
                        $stmt = $this->db->prepare($sql);
						$stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
						$stmt->bindParam(':adminname', $newadminname, PDO::PARAM_STR);
                        $stmt->bindParam(':branchid', $newbranchid, PDO::PARAM_STR);
                        $stmt->bindParam(':statusid', $newstatusid, PDO::PARAM_STR);
                        $stmt->execute();
                    
                        $this->db->commit();
                        
                        $data = [
                            'status' => 'success',
                            'code' => 'RS103',
                            'message' => CustomHandlers::getreSlimMessage('RS103')
						];
						Auth::deleteCache('user-'.$newusername.'-active');
						Auth::deleteCache('user-'.$newusername.'-branchid');
                    } catch (PDOException $e){
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
         * Delete user
         *
         * @return json encoded data
         */
		public function delete(){
			if (Auth::validToken($this->db,$this->token,$this->adminname)){
                $roles = Auth::getRoleID($this->db,$this->token);
                if ($roles == '1'){
                    $newusername = strtolower($this->username);
    				try{
                        $this->db->beginTransaction();
    
                        $sql = "DELETE FROM sys_user WHERE Username = :username;";
                        $stmt = $this->db->prepare($sql);
                        $stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
						
						if ($stmt->execute()) {
    						$data = [
	    						'status' => 'success',
		    					'code' => 'RS104',
			    				'message' => CustomHandlers::getreSlimMessage('RS104')
							];
							Auth::deleteCache('user-'.$newusername.'-active');
							Auth::deleteCache('user-'.$newusername.'-registered');
							Auth::deleteCache('user-'.$newusername.'-branchid');
					    } else {
    						$data = [
	    						'status' => 'error',
		    					'code' => 'RS204',
			    				'message' => CustomHandlers::getreSlimMessage('RS204')
				    		];
						}
						
						$this->db->commit();
                    } catch (PDOException $e){
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
		 * Search all data user paginated
		 * @return result process in json encoded data
		 */
		public function searchUserAsPagination() {
			if (Auth::validToken($this->db,$this->token,$this->username)){
				$newusername = strtolower($this->username);
				$search = "%$this->search%";
				$roles = Auth::getRoleID($this->db,$this->token);
				if ($roles < 3){
					//count total row
					$sqlcountrow = "SELECT count(a.Username) as TotalRow 
						from sys_user a
						inner join core_status b on a.StatusID=b.StatusID
						inner join user_data c on a.Username = c.Username
						where a.Username like :search
						or a.BranchID like :search
						or b.Status like :search
						or c.Fullname like :search
						order by a.Username asc;";
					$stmt = $this->db->prepare($sqlcountrow);		
					$stmt->bindParam(':search', $search, PDO::PARAM_STR);
				} else {
					//count total row
					$sqlcountrow = "SELECT count(x.Username) as TotalRow 
						from (
							select a.Username,a.BranchID,(SELECT a.BranchID FROM sys_user a WHERE a.Username = :username) as UserBranch
							from sys_user a
							inner join core_status b on a.StatusID=b.StatusID
							inner join user_data c on a.Username = c.Username
							where a.Username like :search
							or a.BranchID like :search
							or b.Status like :search
							or c.Fullname like :search
							having a.BranchID = UserBranch
						) x;";
					$stmt = $this->db->prepare($sqlcountrow);
					$stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
					$stmt->bindParam(':search', $search, PDO::PARAM_STR);
				}
				
				if ($stmt->execute()) {	
    	    		if ($stmt->rowCount() > 0){
						$single = $stmt->fetch();
						
						// Paginate won't work if page and items per page is negative.
						// So make sure that page and items per page is always return minimum zero number.
						$newpage = Validation::integerOnly($this->page);
						$newitemsperpage = Validation::integerOnly($this->itemsPerPage);
						$limits = (((($newpage-1)*$newitemsperpage) <= 0)?0:(($newpage-1)*$newitemsperpage));
						$offsets = (($newitemsperpage <= 0)?0:$newitemsperpage);
						
						if ($roles < 3){
							// Query Data
							$sql = "SELECT a.Username,c.Fullname,c.Phone,c.Email,a.BranchID,a.StatusID,b.`Status`,a.Created_at,a.Created_by,a.Updated_at,a.Updated_by
								from sys_user a
								inner join core_status b on a.StatusID=b.StatusID
								inner join user_data c on a.Username = c.Username
								where a.Username like :search
                    			or a.BranchID like :search
			                    or b.Status like :search
								or c.Fullname like :search
								order by a.Username asc LIMIT :limpage , :offpage;";
							$stmt2 = $this->db->prepare($sql);
							$stmt2->bindParam(':search', $search, PDO::PARAM_STR);
							$stmt2->bindValue(':limpage', (INT) $limits, PDO::PARAM_INT);
							$stmt2->bindValue(':offpage', (INT) $offsets, PDO::PARAM_INT);
						} else {
							// Query Data
							$sql = "SELECT a.Username,c.Fullname,c.Phone,c.Email,a.BranchID,a.StatusID,b.`Status`,a.Created_at,a.Created_by,a.Updated_at,a.Updated_by,
									(SELECT a.BranchID FROM sys_user a WHERE a.Username = :username) as UserBranch 
								from sys_user a
								inner join core_status b on a.StatusID=b.StatusID
								inner join user_data c on a.Username = c.Username
								where a.Username like :search
                    			or a.BranchID like :search
			                    or b.Status like :search
								or c.Fullname like :search
								having a.BranchID = UserBranch
								order by a.Username asc LIMIT :limpage , :offpage;";
							$stmt2 = $this->db->prepare($sql);
							$stmt2->bindParam(':username', $newusername, PDO::PARAM_STR);
							$stmt2->bindParam(':search', $search, PDO::PARAM_STR);
							$stmt2->bindValue(':limpage', (INT) $limits, PDO::PARAM_INT);
							$stmt2->bindValue(':offpage', (INT) $offsets, PDO::PARAM_INT);
						}
							
						
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

        /** 
		 * Get data statistic user
		 * @return result process in json encoded data
		 */
		public function statUserSummary() {
			if (Auth::validToken($this->db,$this->token)){
				$newusername = strtolower($this->username);
				$sql = "SELECT 
						(SELECT count(x.Username) FROM sys_user x WHERE x.StatusID='1') AS 'Active',
						(SELECT count(x.Username) FROM sys_user x WHERE x.StatusID='42') AS 'Suspended',
						(SELECT count(x.Username) FROM sys_user x) AS 'Total',
						IFNULL(round((((SELECT Total) - (SELECT Suspended))/(SELECT Total))*100),0) AS 'Percent_Up',
						IFNULL((100 - (SELECT Percent_Up)),0) AS 'Precent_Down';";
				$stmt = $this->db->prepare($sql);
				

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

        
        //STATUS=======================================


		/** 
		 * Get all data Status for user
		 * @return result process in json encoded data
		 */
		public function showOptionStatus() {
			if (Auth::validToken($this->db,$this->token)){
				$sql = "SELECT a.StatusID,a.Status
					FROM core_status a
					WHERE a.StatusID = '1' OR a.StatusID = '42'
					ORDER BY a.Status ASC";
				
				$stmt = $this->db->prepare($sql);		
				$stmt->bindParam(':token', $this->token, PDO::PARAM_STR);

				if ($stmt->execute()) {	
    	    	    if ($stmt->rowCount() > 0){
        	   		   	$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
						$data = [
			   	            'results' => $results, 
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
        
			return JSON::safeEncode($data,true);
	        $this->db= null;
		}
    }