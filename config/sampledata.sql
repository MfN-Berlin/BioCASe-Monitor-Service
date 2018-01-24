DROP TABLE "link_category";
CREATE TABLE "link_category" (
    "id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL, 
    "name" VARCHAR NOT NULL DEFAULT "",
    "logofile" VARCHAR NOT NULL DEFAULT "",
    "logo" BLOB DEFAULT 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAXCAYAAADk3wSdAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAASpJREFUeNqslA0NgzAQhQcGhoRKqAQkIKFzgIRKQELnAAlIQAISOgXsmlyXcr1rCmPJy5L25eP+es2+748zv6ZpFPwZkAatoAUYy8EUoLVC2M5oBnU/3w3AqDWC7wJGLVVQAWjwTmGE6Z29DEw8HWhL7v1fwMTrDj7B1HNAjGpg/FMNdBaAK42YyWiToHlH84bEZnlyPkhQz0CNUBKTfYjANI7JLERFwT4pi8leFAI9AvvCbI7kfMwyJcBo7Avp0g9aCm1h64Tww5Z5JnsmjE2YvVdy9sE0fc02oxE55hF4zEYz3VZMo+GtHk2aMXUC0HLT0zKRK+GMlugNACulr5kx0cxUiCWSuj8xYIelOQWkc+oqlrCrWurFFXYByG5+THk7bB3m1ZT0FWAAPZr2XuxeS4cAAAAASUVORK5CYII=',
    "description" VARCHAR NOT NULL DEFAULT "");

DROP TABLE "count_concept";
CREATE TABLE 'count_concept' (
    "id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL, 
    "position" INTEGER NOT NULL, 
    "institution_id" INTEGER NOT NULL, 
    "xpath" VARCHAR NOT NULL,
    "specifier" INTEGER NOT NULL  DEFAULT 1 , 'timestamp'  DATETIME DEFAULT CURRENT_TIMESTAMP  );

DROP TABLE "auth";
CREATE TABLE 'auth' (
    "username" VARCHAR NOT NULL , 
    "institution_id" INTEGER NOT NULL , 
    "rights" INTEGER  
, 'last_connection' DATETIME);

DROP TABLE "useful_link";
CREATE TABLE 'useful_link' (
    "id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL, 
    "position" INTEGER NOT NULL, 
    "institution_id" INTEGER NOT NULL, 
    "collection_id" INTEGER NOT NULL, 
    "title" VARCHAR NOT NULL,
    "link" VARCHAR NOT NULL , 'is_latest' BOOLEAN);

DROP TABLE "user";
CREATE TABLE 'user' (
    "username" VARCHAR PRIMARY KEY NOT NULL, 
    "fullname" VARCHAR NOT NULL, 
    "email" VARCHAR NOT NULL, 
    "password" VARCHAR DEFAULT '7190a64aec88f656d7beb1dc56c2b423', 'last_connection'  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP  , 'avatar' BLOB);

DROP TABLE "institution";
CREATE TABLE 'institution' (
    "id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL , 
    "name" VARCHAR NOT NULL  UNIQUE , 
    "shortname" VARCHAR NOT NULL  UNIQUE DEFAULT '',   
	"url" VARCHAR NOT NULL  UNIQUE DEFAULT '', 'pywrapper' TEXT DEFAULT NULL, 'active' INTEGER NOT NULL DEFAULT 1);

DROP TABLE "message";
CREATE TABLE 'message' ('short' TEXT PRIMARY KEY NOT NULL DEFAULT '', 'long' TEXT,'target' INTEGER NOT NULL DEFAULT 'fe');

DROP TABLE "collection";
CREATE TABLE 'collection' (
	`id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	`institution_id`	INTEGER NOT NULL,
	`url`	VARCHAR NOT NULL,
	`title`	VARCHAR NOT NULL,
	`title_slug`	VARCHAR NOT NULL,
	`filter`	TEXT NOT NULL,
	`timestamp`	DATETIME DEFAULT CURRENT_TIMESTAMP,
	`preferred_landingpage`	INTEGER DEFAULT 0,
	`landingpage_url`	TEXT NOT NULL DEFAULT '',
	`dataset`	TEXT NOT NULL DEFAULT '',
	`active`	INTEGER NOT NULL DEFAULT 0,
	`alt_pywrapper`	TEXT
,'schema' TEXT DEFAULT 'http://www.tdwg.org/schemas/abcd/2.06');

DROP TABLE "archive";
CREATE TABLE 'archive' (
    "id" INTEGER PRIMARY KEY  AUTOINCREMENT  NOT NULL, 
    "position" INTEGER NOT NULL, 
    "institution_id" INTEGER NOT NULL, 
    "collection_id" INTEGER NOT NULL, 
    "title" VARCHAR NOT NULL,
    "link" VARCHAR NOT NULL , 'is_latest' BOOLEAN);

DROP TABLE "mapping";
CREATE TABLE 'mapping' ('schema_mapping' TEXT, 'source_element' TEXT, 'target_element' TEXT);

DROP TABLE "tag";
CREATE TABLE 'tag' ('shortname' TEXT, 'name' TEXT, 'context' TEXT);

DROP TABLE "rule";
CREATE TABLE 'rule' ('source_element' TEXT,'target_element' TEXT,'schema_mapping' TEXT,'rule' TEXT,'tag' TEXT NOT NULL DEFAULT '', 'weight' TEXT);

DROP TABLE "schema";
CREATE TABLE 'schema' ('id' INTEGER PRIMARY KEY , 'shortname' TEXT, 'urn' TEXT);

DROP TABLE "logbook";
CREATE TABLE 'logbook' (
	`id`	INTEGER PRIMARY KEY AUTOINCREMENT,
	`institution_id` INTEGER DEFAULT 0,
	'schema' INTEGER DEFAULT 1,
	`dsa`	INTEGER DEFAULT 0,
	'action' TEXT,
	'concept' TEXT, 
	'time_elapsed' NUMERIC NOT NULL DEFAULT 0, 
	'timestamp'  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP  , 
FOREIGN KEY (institution_id) REFERENCES institution(id),
FOREIGN KEY (schema) REFERENCES schema(id),
FOREIGN KEY (dsa) REFERENCES collection(id)
);

DROP TABLE "schema_mapping";
CREATE TABLE 'schema_mapping' ('id' INTEGER PRIMARY KEY, 'name' TEXT,'source_schema' TEXT,'target_schema' TEXT);

DROP TABLE "concept";
CREATE TABLE "concept" ('source_element' TEXT, 'source_schema' TEXT,'reference' TEXT DEFAULT '');

DROP INDEX "sqlite_autoindex_user_1";
;
DROP INDEX "sqlite_autoindex_institution_1";
;
DROP INDEX "sqlite_autoindex_institution_2";
;
DROP INDEX "sqlite_autoindex_institution_3";
;
DROP INDEX "sqlite_autoindex_message_1";
;
DROP INDEX "username_ndx";
CREATE INDEX username_ndx ON auth(username);



----
-- populate the database
----
INSERT INTO "message" ("short","long","target") VALUES ('providerError','no connection to provider','3');
INSERT INTO "message" ("short","long","target") VALUES ('archiveFirst','Please provide always the BioCASe Archive and place it first','2');
INSERT INTO "message" ("short","long","target") VALUES ('filterSyntax','Please build the final filter according to the <a target=''biocase.org''  href=''http://www.biocase.org/products/protocols/index.shtml''>required biocase syntax</a>.','2');
INSERT INTO "message" ("short","long","target") VALUES ('notAuthorized','Please log in','2');
INSERT INTO "message" ("short","long","target") VALUES ('noCitation','no citation text provided','1');
INSERT INTO "message" ("short","long","target") VALUES ('concurrentRequests','  requests pending, please be patient and hold on.','2');
INSERT INTO "message" ("short","long","target") VALUES ('warning','<p><span class="glyphicon glyphicon-flash"/> <b>IMPORTANT NOTICE</b></p>
 <p>This is the monitoring service (BMS) for the BioCASe Provider Software (BPS).
 <p>The task of the BMS is to aggregate and present essential infos.
 <p> The BMS does not alter nor remove nor add data retrieved by the BPS.
 <p><b>The BMS needs to contact each BPS and retrieve data from it, 
                hence has to wait for the response, which may take some time. 
               </b>
','3');

-- DUMMY values

INSERT INTO "institution" ("id","name","shortname","url","pywrapper","active")
    VALUES ('1','Dummy Data Denter','DDC','www.ddc.org','biocase.ddc.org','1');

INSERT INTO "collection" ("id","institution_id","url","title","title_slug","filter","timestamp","preferred_landingpage","landingpage_url","dataset","active")
    VALUES ('1','1','http://biocase.ddc.org/pywrapper.cgi?dsa=dummy_dataset','Dummy Dataset','dummy_dataset','<like path="/DataSets/DataSet/Metadata/Description/Representation/Title">Dummy Dataset</like>','2016-02-28 10:21:09','0','','Dummy Dataset','1');

INSERT INTO "count_concept" ("id","position","institution_id", "xpath", "specifier")
    VALUES ('1','1','1','/DataSets/DataSet/Units/Unit/UnitID','7');

INSERT INTO "useful_link" ("position", "institution_id", "collection_id", "title","link")
    VALUES ('1','1','1','Dummy Link','http://www.dummylink.org');

INSERT INTO "user" ("username","fullname","email","password")
    VALUES ('admin','Administrator','admin@mydomain.org','510bd4f81d5fa09c43bb7d4cfd6995de');

INSERT INTO "auth" ("username","institution_id","rights")
    VALUES ('admin', '0', '31');

