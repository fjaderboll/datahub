<?php
	function requireHttps() {
		if(!isInternalIp()) {
			if(empty($_SERVER['HTTPS'])) {
				header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
				exit();
			}
		}
	}

	function getClientIp() {
		return $_SERVER['REMOTE_ADDR'];
	}

	function isInternalIp($ip = null) {
		if($ip == null) {
			$ip = getClientIp();
		}
		return (strpos($ip, "172.16.1.") !== false);
		//return false;
	}

	function time2str($ts) {
	    if(!ctype_digit($ts))
	        $ts = strtotime($ts);

	    $diff = time() - $ts;
	    if($diff == 0)
	        return 'now';
	    elseif($diff > 0)
	    {
	        $day_diff = floor($diff / 86400);
	        if($day_diff == 0)
	        {
	            if($diff < 60) return 'just now';
	            if($diff < 120) return '1 minute ago';
	            if($diff < 3600) return floor($diff / 60) . ' minutes ago';
	            if($diff < 7200) return '1 hour ago';
	            if($diff < 86400) return floor($diff / 3600) . ' hours ago';
	        }
	        if($day_diff == 1) return 'Yesterday';
	        if($day_diff < 7) return $day_diff . ' days ago';
	        if($day_diff < 31) return ceil($day_diff / 7) . ' weeks ago';
	        if($day_diff < 60) return 'last month';
	        return date('F Y', $ts);
	    }
	    else
	    {
	        $diff = abs($diff);
	        $day_diff = floor($diff / 86400);
	        if($day_diff == 0)
	        {
	            if($diff < 120) return 'in a minute';
	            if($diff < 3600) return 'in ' . floor($diff / 60) . ' minutes';
	            if($diff < 7200) return 'in an hour';
	            if($diff < 86400) return 'in ' . floor($diff / 3600) . ' hours';
	        }
	        if($day_diff == 1) return 'Tomorrow';
	        if($day_diff < 4) return date('l', $ts);
	        if($day_diff < 7 + (7 - date('w'))) return 'next week';
	        if(ceil($day_diff / 7) < 4) return 'in ' . ceil($day_diff / 7) . ' weeks';
	        if(date('n', $ts) == date('n') + 1) return 'next month';
	        return date('F Y', $ts);
	    }
	}

	function bytes2str($bytes) {
		$si_prefix = array('B', 'kB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB');
		$base = 1024;
		$class = min((int)log($bytes, $base) , count($si_prefix) - 1);
		return sprintf("%1.2f" , $bytes / pow($base, $class))." ".$si_prefix[$class];
	}

	function seconds2str($seconds) {
		$multiplier = array(1, 60, 60, 24, 7, 4.3482, 12);
		//$unit = array("s", "m", "h", "d", "w", "m", "y");
		$unit = array("sekunder", "minuter", "timmar", "dagar", "veckor", "månader", "år");

		$time = $seconds;
		$i = 0;
		for(; $i < count($multiplier); $i++) {
			$time = $time / $multiplier[$i];
			if(($i == count($multiplier) - 1) || abs($time) < $multiplier[$i+1]) {
				break;
			}
		}
		if($i == 0) {
			return intval($time)." ".$unit[$i];
		} else {
			return intval($time)." ".$unit[$i]." & ".intval(($time - intval($time))*$multiplier[$i])." ".$unit[$i-1];
		}
	}

	function getColor($seed) {
		mt_srand($seed*5);
		return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
	}

	function toTwoDigits($i) {
		return intval($i / 10).($i % 10);
	}

	function getMonthStr($i) {
		$months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
		return $months[$i - 1];
	}

	function formatDate($year, $month, $day) {
		return $year."-".toTwoDigits($month)."-".toTwoDigits($day);
	}

	function jsonDecode($jsonStr) {
		return json_decode($jsonStr, true);
	}

	function jsonEncode($assArray) {
		$debug = getOptionalRequestValue("debug", false);
		$options = ($debug === false ? 0 : JSON_PRETTY_PRINT);
		return json_encode($assArray, $options);
	}

	function jsonRecode($jsonStr) {
		$debug = getOptionalRequestValue("debug", false);
		if($debug === false) {
			return $jsonStr;
		}
		return jsonEncode(jsonDecode($jsonStr));
	}

	function createPasswordSalt() {
		return rand(1000, 9999);
	}

	function createPasswordHash($password, $salt) {
		return md5($password.$salt);
	}

	function generateApiKey() {
		return md5(rand()."a");
	}

	/*function cleanObject(&$object, $attrToKeep) {
		$allAttr = array_keys($object);
		$attrToRemove = array_diff($allAttr, $attrToKeep);
		foreach($attrToRemove as $attr) {
			unset($object[$attr]);
		}
	}*/

	/*function copyObject($object, $attrToCopy) {
		$newObject = array();
		foreach($attrToCopy as $attr) {
			$newObject[$attr] = $object[$attr];
		}
		return $newObject;
	}*/

	function convertFromDbObject($dbObject, $attrToKeep) {
		global $TIMESTAMP_FORMAT_DB, $TIMESTAMP_FORMAT_JSON;

		$newObject = array();
		foreach($attrToKeep as $attr) {
			if(strpos($attr, "timestamp") !== false && $dbObject[$attr] != null) {
				// 2017-12-02 08:00:21 -> 2017-12-02T08:00:21.0Z
				$date = DateTime::createFromFormat($TIMESTAMP_FORMAT_DB, $dbObject[$attr]);
				$newObject[$attr] = $date->format($TIMESTAMP_FORMAT_JSON);
			} else {
				$newObject[$attr] = $dbObject[$attr];
			}
		}
		return $newObject;
	}

	function findFiles($directory, $suffix) {
		$foundFiles = array();
		$files = scandir($directory);
		foreach($files as $file) {
			if($file != "." && $file != "..") {
				$path = $directory."/".$file;
				if(is_file($path)) {
					if(endsWith($file, $suffix)) {
						array_push($foundFiles, $path);
					}
				} else {
					$foundFiles = array_merge($foundFiles, findFiles($path, $suffix));
				}
			}
		}
		return $foundFiles;
	}

	function startsWith($haystack, $needle) {
	     return (substr($haystack, 0, strlen($needle)) === $needle);
	}

	function endsWith($haystack, $needle) {
		return ($needle == "" || substr($haystack, -strlen($needle)) === $needle);
	}

	function toBoolean($intValue) {
		if($intValue == null) {
			return null;
		} else {
			return ($intValue != 0);
		}
	}

	function toDbBoolean($value) {
		if($value == null) {
			return 0;
		} else if(is_int($value)) {
			return ($value != 0 ? 1 : 0);
		} else if(is_string($value)) {
			return (strtolower($value) == "true" ? 1 : 0);
		} else {
			return ($value ? 1 : 0);
		}
	}
