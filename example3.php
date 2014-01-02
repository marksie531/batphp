<html>
<head>
	<title>Table Test</title>
	<link media="screen" type="text/css" href="css/bat.css" rel="stylesheet">
</head>
<body>
<h1>Table Test</h1>

<style>
table.list_table tbody td.beds_info { text-align: left; }
table.list_table tbody td.desc      { text-align: left; }

table.edit_table .beds_info { width: 400px; }
table.edit_table .desc      { width: 400px; height: 50px; }
</style>
<hr/>

<?php
include "bat.php";

// Connect to database
$dbh = mysql_connect ('localhost', 'admin', 'admin');
$selected = mysql_select_db ('grantsni', $dbh) or die( "Unable to select database");

echo '
<a href="example3.php"><button><img src="images/bat/refresh.gif"/> Refresh</button></a>
<a href="example3.php?bat=new"><button><img src="images/bat/add.png"/> New</button></a>
<a href="example3.php?bat=excel"><button><img src="images/bat/excel.png"/> Export (Excel)</button></a><br/><br/>';

// Create query

// [ _flags ] L=Show is list view, S=Sortable (list view only), E=Show in edit view, R=Show as read-only (edit view only), N=New
// [ _val ]   E=Cannot be empty,

$batDef = array (
	'_action' => 'example3.php',
	'_db_table' => 'SCHEME',
	// '_db_list_sql' => $listSql,
	'_can_edit' => true, '_can_delete' => true,	'_can_excel' => false, '_can_pdf' => false, '_can_print' => false,
	'_list_delete' => 1,
	'_default_sort' => 0, '_default_asc' => 1,
	'_debug_sql' => true,
	'_cols' => array (
		0 =>  array ('_lb' => 'Name',                  '_pk' => 'NAME',                   '_fl' => 'LSRN', '_in' => 'text', '_v' => 'E'),
		1 =>  array ('_lb' => 'Summary',               '_cl' => 'SUMMARY',                '_fl' => 'EN',   '_in' => 'textarea', '_v' => 'E', '_class' => 'summary'),
		2 =>  array ('_lb' => 'Description',           '_cl' => 'DESCRIPTION',            '_fl' => 'EN',   '_in' => 'textarea', '_v' => 'E', '_class' => 'description'),
		3 =>  array ('_lb' => 'Exclusions',            '_cl' => 'EXCLUSIONS',             '_fl' => 'EN',   '_in' => 'text', '_v' => 'E'),
		4 =>  array ('_lb' => 'How To Apply',          '_cl' => 'HOWTOAPPLY_ID',          '_fl' => 'LSEN', '_in' => 'select|SELECT ID, DESCRIPTION FROM HOW_TO_APPLY|-1,-', '_v_eq' => '-1'),
		5 =>  array ('_lb' => 'Grants Given',          '_cl' => 'GRANTS_GIVEN',           '_fl' => 'LSEN', '_in' => 'select|-1,-|0..30..1', '_v_eq' => '-1'),
		6 =>  array ('_lb' => 'Year',                  '_cl' => 'YEAR',                   '_fl' => 'LSEN', '_in' => 'select|-1,-|2010..2060..1', '_v_eq' => '-1'),
		7 =>  array ('_lb' => 'Grant Range Id',        '_cl' => 'GRANT_RANGE_ID',         '_fl' => 'EN',   '_in' => 'text'),
		8 =>  array ('_lb' => 'Contact Name',          '_cl' => 'CONTACT_NAME',           '_fl' => 'LSEN', '_in' => 'text'),
		9 =>  array ('_lb' => 'Contact Title',         '_cl' => 'CONTACT_TITLE',          '_fl' => 'EN',   '_in' => 'radio|Mr|Mrs|Miss|Dr|under_grad,Under Grad'),
		10 => array ('_lb' => 'Contact Address 1',     '_cl' => 'CONTACT_ADDRESSS1',      '_fl' => 'EN',   '_in' => 'text'),
		11 => array ('_lb' => 'Contact Address 2',     '_cl' => 'CONTACT_ADDRESSS2',      '_fl' => 'EN',   '_in' => 'text'),
		12 => array ('_lb' => 'Contact Address 3',     '_cl' => 'CONTACT_ADDRESSS3',      '_fl' => 'EN',   '_in' => 'text'),
		13 => array ('_lb' => 'Contact City',          '_cl' => 'CONTACT_CITY',           '_fl' => 'EN',   '_in' => 'text'),
		14 => array ('_lb' => 'Contact Postcode',      '_cl' => 'CONTACT_POSTCODE',       '_fl' => 'EN',   '_in' => 'text'),
		15 => array ('_lb' => 'Contact Country',       '_cl' => 'CONTACT_COUNTRY',        '_fl' => 'EN',   '_in' => 'text'),
		16 => array ('_lb' => 'Contact Telephone',     '_cl' => 'CONTACT_TELEPHONE',      '_fl' => 'EN',   '_in' => 'text'),
		17 => array ('_lb' => 'Contact Email',         '_cl' => 'CONTACT_EMAIL',          '_fl' => 'EN',   '_in' => 'text'),
		18 => array ('_lb' => 'Contact External URL',  '_cl' => 'CONTACT_EXTERNAL_URL',   '_fl' => 'EN',   '_in' => 'text'),
		19 => array ('_lb' => 'Charity Commision URL', '_cl' => 'CHARITY_COMMISSION_URL', '_fl' => 'EN',   '_in' => 'text'),
	),
	'_pagination' => array (
		'_rows_per_page' => 25,
	    '_row_counts' => array (2, 10, 25, 100, 500)
	)
);

doBat ($batDef, $dbh);

mysql_close($dbh);		// close database connection
?>
</body>
</html>