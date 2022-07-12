---------------- tables ---------------
CREATE TABLE user (
    "id" INTEGER PRIMARY KEY AUTOINCREMENT,
    "username" TEXT UNIQUE NOT NULL,
    "password_hash" TEXT NOT NULL,
	"password_salt" TEXT NOT NULL,
    "admin" INTEGER DEFAULT 0 NOT NULL,
	"email" TEXT
);

---------------- views ----------------
