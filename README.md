####  Redcap Importer class and Dependencies 
 By James Hudnall  james.hudnall@gmail.com

 <p>abcd_conn.php - Database Connection Class</p>
 <p>abcd_etc.php - Constants defined for use in system</p>
 <p>class.crud.php - Create, Update, Delete database class which also does other DB operations</p>
 <p>class.reader.php - Class to process JSON files and convert them to MySql</p>
 <p>process.php - page to execure the process of the JSON. Can be made to Cron job if needed.</p> 

SUMMARY: The data sent to json flat files by forms need to be retrieved and converted to the format of a redcap table. 
The above scripts automate the process and will allow selection of directories and files for import. 
