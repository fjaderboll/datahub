---------------- tables ---------------
CREATE TABLE token (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    --"user_id" INTEGER NOT NULL,
    "token" TEXT UNIQUE NOT NULL,
    "enabled" INTEGER DEFAULT 1 NOT NULL,
    "read" INTEGER DEFAULT 1 NOT NULL,
    "write" INTEGER DEFAULT 1 NOT NULL,
    "desc" TEXT

    --FOREIGN KEY(user_id) REFERENCES user(id)
);

CREATE TABLE export (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    --"user_id" INTEGER NOT NULL,
    "export_protocol_id" INTEGER NOT NULL,
    "export_format_id" INTEGER NOT NULL,
    "enabled" INTEGER DEFAULT 1 NOT NULL,
    "url" TEXT,
    "username" TEXT,
    "password" TEXT

    --FOREIGN KEY(user_id) REFERENCES dataset(id),
    --FOREIGN KEY(export_protocol_id) REFERENCES export_protocol(id),
    --FOREIGN KEY(export_format_id) REFERENCES export_format(id)
);

CREATE TABLE node (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
	--"user_id" INTEGER NOT NULL,
    "name" TEXT UNIQUE NOT NULL,
    "desc" TEXT

    --UNIQUE(user_id, name),
	--FOREIGN KEY(user_id) REFERENCES user(id)
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
        FROM sensor
        WHERE node_id = n.id
       ) AS sensor_count,
       (SELECT id
        FROM reading
        WHERE sensor_id IN (SELECT id
                            FROM sensor
                            WHERE node_id = n.id)
        ORDER BY "timestamp" DESC
        LIMIT 1
       ) AS last_reading_id
FROM node n;

CREATE VIEW e_sensor AS
SELECT n.name AS node_name,
       s.*,
       (SELECT count(*)
        FROM reading
        WHERE sensor_id = s.id
       ) AS reading_count,
       (SELECT id
	    FROM reading
	    WHERE sensor_id = s.id
	    ORDER BY "timestamp" DESC
	    LIMIT 1
	   ) AS last_reading_id
FROM sensor s
INNER JOIN node n ON n.id = s.node_id;

CREATE VIEW e_reading AS
SELECT n.name AS node_name,
       s.node_id,
       s.name AS sensor_name,
       s.unit,
       r.*
FROM reading r
JOIN sensor s ON s.id = r.sensor_id
JOIN node n ON n.id = s.node_id;
