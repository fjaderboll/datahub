CREATE TABLE users (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "username" TEXT UNIQUE NOT NULL,
    "password_hash" TEXT NOT NULL,
	"password_salt" TEXT NOT NULL,
    "admin" INTEGER DEFAULT 0 NOT NULL,
	"email" TEXT
);

CREATE TABLE dataset (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "user_id" INTEGER NOT NULL,
    "name" TEXT NOT NULL,
    "desc" TEXT,

    UNIQUE(user_id, name),
    FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE export_type (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "name" TEXT UNIQUE NOT NULL,
    "desc" TEXT NOT NULL
);
INSERT INTO export_type(name, desc) VALUES('HTTP Push - POST', 'Makes a HTTP(S) request using method POST');
INSERT INTO export_type(name, desc) VALUES('MQTT Publish', 'Publish a message to an existing MQTT Broker');

CREATE TABLE export_format (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "name" TEXT UNIQUE NOT NULL,
    "desc" TEXT NOT NULL
);
INSERT INTO export_format(name, desc) VALUES('JSON', 'Standard JSON');
INSERT INTO export_format(name, desc) VALUES('CSV keys/values', 'Keys on line 1, values on line 2');
INSERT INTO export_format(name, desc) VALUES('CSV values', 'Values on line 1');

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
