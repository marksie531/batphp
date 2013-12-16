<html>
<head>
	<title>Table Test</title>
	<link media="screen" type="text/css" href="css/bat.css" rel="stylesheet">
</head>
<body>
<h1>Table Test</h1>

<?php
include "bat.php";

// Connect to database
$dbh = mysql_connect ('localhost', 'admin', 'admin');
$selected = mysql_select_db ('bcm', $dbh) or die( "Unable to select database");

// Create query
$listSql = "SELECT R.ID          ID,
                   R.NAME        NAME,
                   R.DESCRIPTION DESCRIPTION,
                   R.BEDS        BEDS,
                   R.TYPEID      TYPEID,
                   R.SLEEPS      SLEEPS,
                   R.MIN_SLEEPS  MIN_SLEEPS,
                   T.DESCRIPTION TYPE_DESC,
                   R.RATE_TYPE   RATE_TYPE
              FROM ROOM R, TYPE T
             WHERE T.ID = R.TYPEID";

// [ Flags ] L=Show is list view, S=Sortable (list view only), E=Show in edit view, R=Show as read-only (edit view only)
$batDef = array (
	'_action' => 'test1.php',
	'_db_table' => 'ROOM',
	'_db_list_sql' => $listSql,
	'_can_edit' => true, '_can_delete' => true,	'_can_excel' => true, '_can_pdf' => false, '_can_print' => false,
	'_page_limit' => 30,
	'_list_delete' => 1,
	'_list_id' => 'rooms',
	'_edit_id' => 'room',
	'_cols' => array (
		0 => array ('_lb' => 'ID',          '_pkc' => 'ID',          '_flags' => 'LSR', '_input' => 'text'),
		1 => array ('_lb' => 'Name',        '_col' => 'NAME',        '_flags' => 'LSE', '_input' => 'text'),
		2 => array ('_lb' => 'Min.Sl',      '_col' => 'MIN_SLEEPS',  '_flags' => 'LSE', '_input' => 'text'),
		3 => array ('_lb' => 'Sleeps',      '_col' => 'SLEEPS',      '_flags' => 'LSE', '_input' => 'text'),
		4 => array ('_lb' => 'Description', '_col' => 'DESCRIPTION', '_flags' => 'LSE', '_input' => 'textarea', '_class' => 'desc'),
		5 => array ('_lb' => 'Type',        '_col' => 'TYPE_DESC',   '_flags' => 'LS'),
		6 => array ('_lb' => 'Type',        '_col' => 'TYPEID',      '_flags' => 'E',   '_input' => 'combo_sql|SELECT ID,DESCRIPTION FROM TYPE'),
	),
	'_validation' => array (
	    0 => array ('_regex' => '', '_msg' => 'Please specify text for '),
	)
);

doBat ($batDef, $dbh);

mysql_close($dbh);		// close database connection
?>
</body>
</html>