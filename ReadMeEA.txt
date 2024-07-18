In order to allow uploading large files, modify the php.ini
In your PHP configuration (usually found in php.ini), you'll need to adjust several settings related to file uploads:

upload_max_filesize: Increase this value to allow larger file uploads. For example, set it to 100M to allow file uploads up to 100 megabytes.
post_max_size: Make sure this value is larger than upload_max_filesize. For example, set it to 110M.
max_execution_time: Increase the maximum execution time to accommodate longer file uploads. For example, set it to 3600 (1 hour).
max_input_time: Also, increase the maximum input time to match the execution time. Set it to 3600.


In order to let the fetch on client side see custom headers returned from the API, set those headers in cors.php->'exposed_headers'. In our case, we have set the _token header and the 'Content-Disposition' header that allows the client to check the file name whenever sending an excel report.

In order to allow the client sending custom headers to the API change the cors.php->allowed_headers


Database Backup Procedure:

1-Creating Command
php artisan make:command DatabaseBackup (Check the file in app/Console/Commands)
this backup script uses mysqldump utility to create a DB backup gzip file.
Note that mysqldump exists by default on CPANEL, and may not exist on development PC if WAMP or MAMP are used

2-Executing the command
In app/Console/kernel.php, we execute the DatabaseBackup script within the schedule function,
this schedule will be run on Cpanel using a CRON job:
below is the CPANEL Cron job definition (run every 1 minute configuration)
Minute	Hour	Day	    Month	Weekday	    Command
*	    *	    *	    *	    *	         cd /home/codefolio/public_html/testAPI/ && php artisan schedule:run >> /dev/null 2>&1


3-The CRON job must be set to run every minute, and then Laravel scheduler will evaluate all scheduled tasks and determine if they need to run based on the server's current time.



To be able to read images from public storage, a symbolic link must be created from storage/app/public to /public folder
In a development environment this can be done through "php artisan storage:link"
When deploying on cpanel, we have to make the same operation. Since SSH access is disable, we have to run a CRON Job that executes the command below.
(Make sure that the CRON job is run exactly once)

ln -s /home/codefolio/menu.codefolio.site/MenuAPI/storage/app/public         /home/codefolio/menu.codefolio.site/MenuAPI/public/storage

To make sure that the operation worked, a symbolic link should appear in the destination folder (Which is the second parameter of the command so in this case a symbolic link folder named storage should appear under public folder )
