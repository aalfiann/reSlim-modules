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
use \classes\CustomHandlers as CustomHandlers;
use PDO;
	/**
     * A class for utilities
     *
     * @package    Enterprise Utilities
     * @author     M ABD AZIZ ALFIAN <github.com/aalfiann>
     * @copyright  Copyright (c) 2018 M ABD AZIZ ALFIAN
     * @license    https://github.com/aalfiann/reSlim-b2b/blob/master/license.md  MIT License
     */
	class Util {

        /**
		 * Determine if user is already registered or not
         * 
         * @param $db : Dabatase connection (PDO)
         * @param $username : input the username
		 * @return boolean true / false
		 */
		public static function isUserRegistered($db,$username){
            $r = false;
            $newusername = strtolower($username);
            if (Auth::isKeyCached('user-'.$newusername.'-registered',86400)){
                $r = true;
            } else {
                $sql = "SELECT a.Username
			    	FROM sys_user a 
				    WHERE a.Username = :username;";
    			$stmt = $db->prepare($sql);
	    		$stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
		    	if ($stmt->execute()) {	
                	if ($stmt->rowCount() > 0){
                        $r = true;
                        Auth::writeCache('user-'.$newusername.'-registered');
        	        }          	   	
	    		}
            } 		
			return $r;
			$db = null;
        }
        
        /**
		 * Determine if user is active or not
         * 
         * @param $db : Dabatase connection (PDO)
         * @param $username : input the username
		 * @return boolean true / false
		 */
		public static function isUserActive($db,$username){
            $r = false;
            $newusername = strtolower($username);
            if (Auth::isKeyCached('user-'.$newusername.'-active',86400)){
                $r = true;
            } else {
                $sql = "SELECT a.Username
			    	FROM sys_user a 
				    WHERE a.Username = :username AND a.StatusID='1';";
    			$stmt = $db->prepare($sql);
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
         * 
         * @param $db : Dabatase connection (PDO)
         * @param $username : input the username
		 * @return boolean true / false
		 */
		public static function isMainUserExist($db,$username){
            $r = false;
            $newusername = strtolower($username);
            if (Auth::isKeyCached('user-'.$newusername.'-exists',86400)){
                $r = true;
            } else {
                $sql = "SELECT a.Username
			    	FROM user_data a 
				    WHERE a.Username = :username;";
    			$stmt = $db->prepare($sql);
	    		$stmt->bindParam(':username', $newusername, PDO::PARAM_STR);
		    	if ($stmt->execute()) {	
                	if ($stmt->rowCount() > 0){
                        $r = true;
                        Auth::writeCache('user-'.$newusername.'-exists');
        	        }          	   	
	    		}
            }	 		
			return $r;
			$db = null;
		}

        /** 
         * Get information branchid user by username
         *
         * @param $db : Dabatase connection (PDO)
         * @param $username : input the username
         * @return string BranchID
         */
        public static function getUserBranchID($db,$username){
            $roles = "";
            $newusername = strtolower($username);
            if (Auth::isKeyCached('user-'.$newusername.'-branchid',86400)){
                $data = json_decode(Auth::loadCache('user-'.$newusername.'-branchid'));
                if (!empty($data)){
                    $roles = $data->Role;
                }
            } else {
                $sql = "SELECT a.BranchID FROM sys_user a WHERE a.Username =:username limit 1;";
	    		$stmt = $db->prepare($sql);
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
			$db = null;
        }
    }