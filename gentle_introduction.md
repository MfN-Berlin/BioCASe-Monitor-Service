
#  BioCASe Monitor 2.1


The BMS is a web application relying on jQuery 2.1.4, Bootstrap 3.3.7 and a Sqlite3 Database.
It is designed to be a GUI aggregation tool for different BioCASe Provider Software installations (BPS).
There are 3 entry points: for the backend, the frontend, and the webservices.
Each entrypoint is a  index.php file which acts as a controller for dispatching the routes,
and builds the html skeleton. 
Further processing is managed by a javascript file defining functions to populate the skeleton.

The database holds general metadata about the data providers,  infos about the data to be fetched, 
and expert knowledge about the schemas, their concepts, their mappings, and the rules to be applied.
Furthermore, credentials for the backend access are stored here.

## Database

Infos of the Data Providers include:
- id, name, shortname, institution Url, BPS Url, status
- Datasource access points
- Concepts to be counted
- XML archives
- useful links

Expert Knowledge:
- schemas with shortname and Urn
- mappings between schemas
- mapped elements for each schema mapping
- rules for each schema element



## Backend

Access to the backend is only granted to registered users. 
Each user has only editing permissions to his associated data provider.
The basic metadata, the count concepts and the Datasource Access Points, 
together with their archives and useful links, are loaded from the database
and displayed in a tabbed view.
For each Datasource access point (DSA) CURL requests are made to the BPS, 
in order to help to fill in the needed additional data from a dropdown-list
such as the BPS Url, the DataSet, and the schema.
The only field with free text input is the title field.
These CURL requests are operated by a PHP script and launched via AJAX. 
There are about 6 AJAX requests per DSA,
so depending on the response time of the BPS server, you will have to wait a few seconds.
A progress bar showing the pending AJAX requests is displayed in real time.

The backend has CRUD functionalities to manage DSAs.
The CRUD functions are written in PHP, with PDO to access the database.
They are called via AJAX, and triggered by click events on the appropriate buttons.


## Frontend

function frontController($route) {
  791         switch ($route) {
  792             case 'getMessages':
  793                 echo $this->getMessages();
  794                 exit;
  795 
  796             case 'getSchema':
  797                 $schema = filter_input(INPUT_GET, 'schema');
  798                 echo $this->getSchema($schema);
  799                 exit;
  800 
  801             case 'getCurrentRecords':
  802                 $providerId = filter_input(INPUT_GET, 'idProvider');
  803                 $schema = filter_input(INPUT_GET, 'schema');
  804                 $url = filter_input(INPUT_GET, 'url');
  805                 $filter = filter_input(INPUT_GET, 'filter');
  806                 $nocache = intval(filter_input(INPUT_GET, 'nocache'));
  807                 echo $this->getCurrentRecords($providerId, $schema, $url, $filter, $nocache);
  808                 exit;
  809 
  810             case 'getCountConcepts':
  811                 $providerId = filter_input(INPUT_GET, 'idProvider');
  812                 $schema = filter_input(INPUT_GET, 'schema');
  813                 $url = filter_input(INPUT_GET, 'url');
  814                 $concept = filter_input(INPUT_GET, 'concept');
  815                 $specifier = filter_input(INPUT_GET, 'specifier');
  816                 $filter = filter_input(INPUT_GET, 'filter');
  817                 $nocache = intval(filter_input(INPUT_GET, 'nocache'));
  818                 echo $this->getCountConcepts($providerId, $schema, $url, $concept, $specifier, $filter, $nocache);
  819                 exit;
  820 
  821             case 'getCitation':
  822                 $providerId = filter_input(INPUT_GET, 'idProvider');
  823                 $url = filter_input(INPUT_GET, 'url');
  824                 $filter = filter_input(INPUT_GET, 'filter');
  825                 $cached = intval(filter_input(INPUT_GET, 'cached'));
  826                 echo $this->getCitation($providerId, $url, $filter, $cached);
  827                 exit;
  828 
  829             case 'getConcepts':
  830                 $providerId = filter_input(INPUT_GET, 'idProvider');
  831                 echo $this->getConcepts($providerId);
  832                 exit;
  833 
  834             case 'getMaxCalls':
  835                 $providerId = filter_input(INPUT_GET, 'idProvider');
  836                 echo $this->getMaxCalls($providerId);
  837                 exit;
  838 
  839             case 'getTotalMaxCalls':
  840                 echo $this->getTotalMaxCalls();
  841                 exit;
  842 
  843             case 'getProviderMainData':
  844                 $providerId = filter_input(INPUT_GET, 'idProvider');
  845                 echo $this->getProviderMainData($providerId);
  846                 exit;
  847 
  848             default:
  849 // display start page
  850 //                $content_type = "text/html";
  851 //                header('Content-type: ' . $content_type . ', charset=utf-8');
  852         }
  853     }
  854 

## Webservices


