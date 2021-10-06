<?php
    $db = null;

    function openDatabaseConnection($datasetId = null) {
        global $DB_MAIN_FILE, $DB_SETUP_MAIN_SQL, $db;

        if(!file_exists($DB_MAIN_FILE)) {
            initBlankDatabase($DB_MAIN_FILE, $DB_SETUP_MAIN_SQL);
        }

        if($db == null) {
            if(isUser()) {
                $db = openDatabaseFile($DB_MAIN_FILE);
                if($datasetId != null || isDataset()) {
                    $db->exec("ATTACH DATABASE ".getDatasetFilename($datasetId)." AS ds");
                }
            } else if($datasetId != null || isDataset()) {
                $db = openDatabaseFile(getDatasetFilename($datasetId));
            } else {
                $db = openDatabaseFile($DB_MAIN_FILE);
            }
        }
    }

    function openDatabaseFile($file) {
        $localDb = new SQLite3($file);
        $localDb->enableExceptions(true);
        $localDb->busyTimeout(3000);
        $localDb->exec("PRAGMA foreign_keys = ON"); // enforce foreign keys
        $localDb->exec("BEGIN");

        return $localDb;
    }

    function initBlankDatabase($dbFile, $setupSqlFile) {
        $sql = file_get_contents($setupSqlFile);

        $newDb = openDatabaseFile($dbFile);
        $newDb->exec($sql);
        $newDb->exec("COMMIT");
        $newDb->close();
    }

    function getDatasetFilename($datasetId = null) {
        global $DB_DATASET_DIR;
        if($datasetId == null) {
            $datasetId = getDatasetId();
        }
        return $DB_DATASET_DIR.$datasetId.".db";
    }

    function initDatasetDatabase($datasetId) {
        global $DB_SETUP_DATASET_SQL;
        initBlankDatabase(getDatasetFilename($datasetId), $DB_SETUP_DATASET_SQL);
    }

    function removeDatasetDatabase($datasetId) {
        unlink(getDatasetFilename($datasetId));
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
