####  Redcap Importer class and Dependencies 
 By James Hudnall  james.hudnall@gmail.com
 
 Code has been simplified into two files with a config file holding API tokens and url info
 
 <p>turn.php - cron job that will process files and import data
 <p>class.reader.php - Class to process JSON files and convert import them to Redcap</p>
 <p>create-ded.php - Creates CSV file that builds instrument for Direct Discounting</p>
 <p>create-lmt.php -Creates CSV file that builds instrument for Little Man Task</p>
 <p>create-str.php -Creates CSV file that builds instrument for Stroop</p>
 

SUMMARY: The data sent to json flat files by forms need to be retrieved and converted to the format of a redcap table. 
The above scripts automate the process and will allow selection of directories and files for import. 
