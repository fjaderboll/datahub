---------------- tables ---------------
CREATE TABLE user (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "username" TEXT UNIQUE NOT NULL,
    "password_hash" TEXT NOT NULL,
	"password_salt" TEXT NOT NULL,
    "admin" INTEGER DEFAULT 0 NOT NULL,
	"email" TEXT
);

CREATE TABLE export_protocol (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "name" TEXT UNIQUE NOT NULL,
    "desc" TEXT NOT NULL
);
INSERT INTO export_protocol(name, desc) VALUES('HTTP Push - POST', 'Makes a HTTP(S) request using method POST');
INSERT INTO export_protocol(name, desc) VALUES('MQTT Publish', 'Publish a message to an existing MQTT Broker');

CREATE TABLE export_format (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "name" TEXT UNIQUE NOT NULL,
    "desc" TEXT NOT NULL
);
INSERT INTO export_format(name, desc) VALUES('JSON', 'Standard JSON');
INSERT INTO export_format(name, desc) VALUES('CSV keys/values', 'Keys on line 1, values on line 2');
INSERT INTO export_format(name, desc) VALUES('CSV values', 'Values on line 1');

---------------- views ----------------
