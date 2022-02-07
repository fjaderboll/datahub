<?php
    $db = null;

    function openDatabaseConnection($openMain, $userId = null) {
        global $DB_MAIN_FILE, $DB_SETUP_MAIN_SQL, $db;

        if(!file_exists($DB_MAIN_FILE)) {
            initBlankDatabase($DB_MAIN_FILE, $DB_SETUP_MAIN_SQL);
        }

        if($db == null) {
            if($openMain) {
                $db = openDatabaseFile($DB_MAIN_FILE, true);
                if($userId != null) {
                    $db->exec("ATTACH DATABASE '".getUserDatabaseFilename($userId)."' AS ds");
                    $dbAttached = true;
                }
            } else if($userId !== null) {
                $db = openDatabaseFile(getUserDatabaseFilename($userId), true);
            }
        }
    }

    function openDatabaseFile($file, $existingDatabase) {
        if($existingDatabase && !file_exists($file)) {
            throw new Exception("Cannot open non-existing database: $file");
        }
        $localDb = new SQLite3($file);
        $localDb->enableExceptions(true);
        $localDb->busyTimeout(3000);
        $localDb->exec("PRAGMA foreign_keys = ON"); // enforce foreign keys
        $localDb->exec("BEGIN");

        return $localDb;
    }

    function initBlankDatabase($dbFile, $setupSqlFile) {
        $sql = file_get_contents($setupSqlFile);

        $newDb = openDatabaseFile($dbFile, false);
        $newDb->exec($sql);
        $newDb->exec("COMMIT");
        $newDb->close();
    }

    function getUserDatabaseFilename($userId = null) {
        global $DB_USER_DIR;
        return $DB_USER_DIR.$userId.".db";
    }

    function initUserDatabase($userId) {
        global $DB_USER_DIR, $DB_SETUP_USER_SQL;
        mkdir($DB_USER_DIR);
        initBlankDatabase(getUserDatabaseFilename($userId), $DB_SETUP_USER_SQL);
    }

    function removeUserDatabase($userId) {
        unlink(getUserDatabaseFilename($userId));
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
