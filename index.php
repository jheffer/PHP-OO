<?php
// includes
include('settings.php');
include('classes.php');

// database of .htaccess files
$htFiles = new htList();		// create a database of .htaccess files
$htFiles->htScan($cgi_path);		// populate the database

// user database
$users = new userList();			// create a user database
//$users->add('John Doe');			// add a user to the database
//$users->exists('John Doe');			// did user add succesfully?
$users->scanUsers($htFiles->htArray());		// populate users
$users->scanScripts($htFiles->htArray());	// populate scripts for each user
?>
<html>
<head>
	<title>User access summary</title>
</head>
<body>
<h1>.htaccess file parser</h1>
<!--<h2>.htaccess files</h2>
<pre>
<?php
print_r($htFiles);
?>
</pre>-->
<h2>Users</h2>
<?php
$users->htmlSummary();		// print contents of user database in HTML
?>
</body>
</html>