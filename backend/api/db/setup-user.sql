---------------- tables ---------------
CREATE TABLE token (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "token" TEXT UNIQUE NOT NULL,
    "enabled" INTEGER DEFAULT 1 NOT NULL,
    "read" INTEGER DEFAULT 1 NOT NULL,
    "write" INTEGER DEFAULT 1 NOT NULL,
    "desc" TEXT
);

CREATE TABLE export (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "enabled" INTEGER DEFAULT 1 NOT NULL,
    "protocol" TEXT NOT NULL,
    "format" TEXT NOT NULL,
    "url" TEXT,
    "auth1" TEXT,
    "auth2" TEXT,
    "fail_count" INTEGER DEFAULT 0 NOT NULL,
    "status" TEXT
);

CREATE TABLE node (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
	"name" TEXT UNIQUE NOT NULL,
    "desc" TEXT,
    "reading_count" INTEGER NOT NULL DEFAULT 0,
    "last_reading_id" INTEGER,

    FOREIGN KEY(last_reading_id) REFERENCES reading(id)
);

CREATE TABLE sensor (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
	"node_id" INTEGER NOT NULL,
    "name" TEXT NOT NULL,
	"desc" TEXT,
	"unit" TEXT,
    "reading_count" INTEGER NOT NULL DEFAULT 0,
    "last_reading_id" INTEGER,

    UNIQUE(node_id, name),
	FOREIGN KEY(node_id) REFERENCES node(id),
    FOREIGN KEY(last_reading_id) REFERENCES reading(id)
);
CREATE INDEX sensor_ix_node_id ON sensor(node_id);

CREATE TABLE reading (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "sensor_id" INTEGER NOT NULL,
    "value" REAL NOT NULL,
    "timestamp" TIMESTAMP DEFAULT (datetime('now', 'localtime')) NOT NULL,

    FOREIGN KEY(sensor_id) REFERENCES sensor(id)
);
CREATE INDEX reading_ix_sensor_id ON reading(sensor_id);
CREATE INDEX reading_ix_timestamp ON reading(timestamp DESC);

---------------- views ----------------
CREATE VIEW e_node AS
SELECT n.*,
       (SELECT count(*)
        FROM sensor s
        WHERE s.node_id = n.id
       ) AS sensor_count
FROM node n;

CREATE VIEW e_sensor AS
SELECT n.name AS node_name,
       s.*
FROM sensor s
JOIN node n ON n.id = s.node_id;

CREATE VIEW e_reading AS
SELECT n.name AS node_name,
       s.node_id,
       s.name AS sensor_name,
       s.unit,
       r.*
FROM reading r
JOIN sensor s ON s.id = r.sensor_id
JOIN node n ON n.id = s.node_id;
