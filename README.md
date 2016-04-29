####  Redcap Importer class and Dependencies 
 By James Hudnall  james.hudnall@gmail.com
 
 Code has been simplified into two files with a config file holding API tokens and url info
 
 <p>turn.php - cron job that will process files and import data
 <p>class.reader.php - Class to process JSON files and convert them to MySql</p>
 

SUMMARY: The data sent to json flat files by forms need to be retrieved and converted to the format of a redcap table. 
The above scripts automate the process and will allow selection of directories and files for import. 
