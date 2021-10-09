
CREATE TABLE dataset_token (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "dataset_id" INTEGER NOT NULL,
    "token" TEXT UNIQUE NOT NULL,
    "enabled" INTEGER DEFAULT 1 NOT NULL,
    "read" INTEGER DEFAULT 1 NOT NULL,
    "write" INTEGER DEFAULT 1 NOT NULL,
    "desc" TEXT

    --FOREIGN KEY(dataset_id) REFERENCES dataset(id)
);

CREATE TABLE dataset_export (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "dataset_id" INTEGER NOT NULL,
    "export_type_id" INTEGER NOT NULL,
    "export_format_id" INTEGER NOT NULL,
    "enabled" INTEGER DEFAULT 1 NOT NULL,
    "url" TEXT

    --FOREIGN KEY(dataset_id) REFERENCES dataset(id),
    --FOREIGN KEY(export_type_id) REFERENCES export_type(id),
    --FOREIGN KEY(export_format_id) REFERENCES export_format(id)
);

CREATE TABLE node (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
	"dataset_id" INTEGER NOT NULL,
    "name" TEXT UNIQUE NOT NULL,
    "desc" TEXT

	--FOREIGN KEY(dataset_id) REFERENCES dataset(id)
);

CREATE TABLE sensor (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
	"node_id" INTEGER NOT NULL,
    "name" TEXT NOT NULL,
	"desc" TEXT,
	"unit" TEXT,

    UNIQUE(node_id, name),
	FOREIGN KEY(node_id) REFERENCES node(id)
);

CREATE TABLE reading (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "sensor_id" INTEGER NOT NULL,
    "value" REAL NOT NULL,
    "timestamp" TIMESTAMP DEFAULT (datetime('now', 'localtime')) NOT NULL,

    FOREIGN KEY(sensor_id) REFERENCES sensor(id)
);

---------------- views ----------------
/*
CREATE VIEW e_dataset AS
SELECT d.*,
       ud.user_id,
       ud.permission_admin,
       ud.permission_push,
       (SELECT max(r.timestamp)
        FROM node n,
             sensor s,
             reading r
        WHERE n.dataset_id = d.id
        AND s.node_id = n.id
        AND s.last_reading_id = r.id
       ) AS last_reading_timestamp
FROM dataset d,
     user_dataset ud
WHERE d.id = ud.dataset_id;

CREATE VIEW e_user_dataset AS
SELECT ud.*,
       u.username
FROM user_dataset ud,
     users u
WHERE ud.user_id = u.id;

CREATE VIEW e_node AS
SELECT n.*,
       (SELECT max(r.timestamp)
        FROM sensor s,
             reading r
        WHERE s.node_id = n.id
        AND s.last_reading_id = r.id
       ) AS last_reading_timestamp
FROM node n;

CREATE VIEW e_reading AS
SELECT n.dataset_id,
       s.node_id,
       r.*
FROM reading r,
     sensor s,
     node n
WHERE r.sensor_id = s.id
AND s.node_id = n.id;

CREATE VIEW e_sensor AS
SELECT n.dataset_id,
       s.*,
       (SELECT "timestamp" FROM reading WHERE id = s.last_reading_id) AS last_reading_timestamp
FROM sensor s,
     node n,
     dataset d
WHERE s.node_id = n.id
AND n.dataset_id = d.id;

CREATE VIEW e_event_config AS
SELECT n.dataset_id,
       s.node_id,
       ec.*
FROM event_config ec,
     sensor s,
     node n
WHERE ec.sensor_id = s.id
AND s.node_id = n.id;

CREATE VIEW e_event AS
SELECT n.dataset_id,
       s.node_id,
       r.sensor_id,
       e.*,
       r."timestamp" AS reading_timestamp,
       r.value AS reading_value
FROM event e,
     reading r,
     sensor s,
     node n
WHERE e.reading_id = r.id
AND r.sensor_id = s.id
AND s.node_id = n.id;
*/
