<?php
    $db = null;

    function openDatabaseConnection() {
        global $DB_FILE, $db;

        if(!isset($db) || $db == null) {
            $firstTime = !file_exists($DB_FILE);

            $db = new SQLite3($DB_FILE);
            $db->enableExceptions(true);
            $db->busyTimeout(3000);
            $db->exec("PRAGMA foreign_keys = ON"); // enforce foreign keys
            $db->exec("BEGIN");

            if($firstTime) {
                initNewDatabase();
            }
        }
    }

    function initNewDatabase() {
        global $DB_SETUP_SQL, $db;

        $sql = file_get_contents($DB_SETUP_SQL);
        $db->exec($sql);
        commitDatabaseConnection();
    }

    function rollbackDatabaseConnection() {
        global $db;
        $db->exec("ROLLBACK");
        $db->exec("BEGIN");
    }

    function commitDatabaseConnection() {
        global $db;
        $db->exec("COMMIT");
        $db->exec("BEGIN");
    }

    function closeDatabaseConnection() {
        global $db;

        if(isset($db)) {
            if($db != null) {
                rollbackDatabaseConnection();
                $db->close();
            }
            unset($db);
        }
    }

    function dbUpdate($sql /* [arg1 [,arg2[,...]]] */) {
        global $db;

        $stmt = $db->prepare($sql);
        if($stmt) {
			$args = func_get_args();
			$i = 0;
			foreach($args as $arg) {
				if($i > 0) { // skip first argument which is $sql
					$stmt->bindValue($i, $arg, getDbType($arg));
				}
				$i++;
			}
			$success = $stmt->execute();
			if($success) {
				return $db->changes();
			}
            throw new Exception("Error executing statement");
		}
        throw new Exception("Error creating statement");
    }

    function dbGetLastId() {
        global $db;
        return $db->lastInsertRowID();
    }

    function dbQuery($sql /* [arg1 [,arg2[,...]]] */) {
        global $db;

        $stmt = $db->prepare($sql);
        if($stmt) {
			$args = func_get_args();
			$i = 0;
			foreach($args as $arg) {
				if($i > 0) { // skip first argument which is $sql
					$stmt->bindValue($i, $arg, getDbType($arg));
				}
				$i++;
			}
			$resultSet = $stmt->execute();
			if($resultSet) {
				$rows = array();
				while($row = $resultSet->fetchArray()) {
					array_push($rows, $row);
				}
				return $rows;
			}
		}
		return false;
    }

    function dbQuerySingle($sql /* [arg1 [,arg2[,...]]] */) {
        $args = func_get_args();
        $rows = call_user_func_array("dbQuery", $args);
        if(count($rows) == 1) {
            return $rows[0];
        } else {
            throw new Exception("Expected one result row, but got ".count($rows)." rows");
        }
    }

    function getDbType($arg) {
		$phpType = gettype($arg);
		if($phpType == "string")       return SQLITE3_TEXT;
		else if($phpType == "integer") return SQLITE3_INTEGER;
		else if($phpType == "double")  return SQLITE3_FLOAT;
		else if($phpType == "NULL")    return SQLITE3_NULL;
		else die("Unsupported type \"$phpType\"");
	}
