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
    "name" TEXT UNIQUE NOT NULL,
    "desc" TEXT
);

CREATE TABLE user_dataset (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
	"user_id" INTEGER NOT NULL,
    "dataset_id" INTEGER NOT NULL,
    "permission_admin" INTEGER DEFAULT 0 NOT NULL,
    "permission_push" INTEGER DEFAULT 0 NOT NULL,

	FOREIGN KEY(user_id) REFERENCES users(id),
	FOREIGN KEY(dataset_id) REFERENCES dataset(id)
);

CREATE TABLE node (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
	"dataset_id" INTEGER NOT NULL,
    "key" TEXT UNIQUE,
    "name" TEXT NOT NULL,
    "location" TEXT,
	"desc" TEXT,

	FOREIGN KEY(dataset_id) REFERENCES dataset(id)
);

CREATE TABLE sensor (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
	"node_id" INTEGER NOT NULL,
    "key" TEXT UNIQUE NOT NULL,
    "name" TEXT NOT NULL,
	"desc" TEXT,
	"value_unit" TEXT,
	"discrete_values" INTEGER DEFAULT 0 NOT NULL,
    "readings_count" INTEGER DEFAULT 0 NOT NULL,
	"readings_max" INTEGER DEFAULT 1000 NOT NULL,
	"last_reading_id" INTEGER,

	FOREIGN KEY(node_id) REFERENCES node(id),
	FOREIGN KEY(last_reading_id) REFERENCES reading(id)
);

CREATE TABLE reading (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "sensor_id" INTEGER NOT NULL,
    "value" REAL NOT NULL,
    "timestamp" TIMESTAMP DEFAULT (datetime('now', 'localtime')) NOT NULL,

    FOREIGN KEY(sensor_id) REFERENCES sensor(id)
);

CREATE TABLE event_config (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "user_id" INTEGER NOT NULL,
    "sensor_id" INTEGER NOT NULL,
    "enabled" INTEGER DEFAULT 0 NOT NULL,
    "value" REAL,
    "below" INTEGER,
    "equal" INTEGER,
    "repeat" INTEGER DEFAULT 0 NOT NULL,
    "send_email" INTEGER DEFAULT 0 NOT NULL,
    "email" TEXT,
    "push" INTEGER DEFAULT 0 NOT NULL,
    "push_method" TEXT,
    "push_url" TEXT,

    FOREIGN KEY(user_id) REFERENCES users(id),
    FOREIGN KEY(sensor_id) REFERENCES sensor(id)
);

CREATE TABLE event (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "user_id" INTEGER NOT NULL,
    "reading_id" INTEGER NOT NULL,
    "value" REAL,
    "below" INTEGER,
    "equal" INTEGER,
    "email" TEXT,
    "confirmed" INTEGER DEFAULT 0 NOT NULL,

    FOREIGN KEY(user_id) REFERENCES users(id),
    FOREIGN KEY(reading_id) REFERENCES reading(id)
);

---------------- views ----------------

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
