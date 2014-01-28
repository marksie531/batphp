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
<a href="example3.php?bat=export"><button><img src="images/bat/export.png"/> Export</button></a>
<a href="example3.php?bat=import"><button><img src="images/bat/import.png"/> Import</button></a><br/><br/>';

// Create query

// [ _flags ] L=Show is list view, S=Sortable (list view only), E=Show in edit view, R=Show as read-only (edit view only), N=New
// [ _val ]   E=Cannot be empty,

$listSql = "select * from (
select
	s.name                   name,
	s.exclusions             exclusions,
	s.howtoapply_id          howtoapply_id,
	s.grants_given           grants_given,
	s.year                   year,
	s.grant_range_id         grant_range_id,
	s.contact_title          contact_title,
	s.contact_name           contact_name,
	s.contact_addresss1      contact_addresss1,
	s.contact_addresss2      contact_addresss2,
	s.contact_addresss3      contact_addresss3,
	s.contact_postcode       contact_postcode,
	s.contact_country        contact_country,
	s.contact_telephone      contact_telephone,
	s.contact_email          contact_email,
	s.contact_external_url   contact_external_url,
	s.charity_commission_url charity_commission_url,
	s.summary                summary,
	s.description            description,
	hta.description          howtoapply_desc,
	ifnull(group_concat(t.description), '') as topic_desc
from
	scheme s
inner join
	how_to_apply hta on (s.howtoapply_id = hta.id)
left join
	scheme_topic st on (s.name = st.scheme_id)
left join
	topic t on (st.topic_id = t.id)
group by s.name
) m";

$batDef = array (
	'_action' => 'example3.php',
	'_db_table' => 'scheme',
	'_db_list_sql' => $listSql,
	'_can_edit' => true, '_can_delete' => true,	'_can_excel' => false, '_can_pdf' => false, '_can_print' => false,
	'_list_delete' => 'name',
	'_default_sort' => 0, '_default_asc' => 1,
	'_debug_sql' => false,
	'_cols' => array (
		array ('_lb' => 'Name',                  '_pk' => 'name',                   '_fl' => 'LSRNX', '_in' => 'text', '_v' => 'E'),
		array ('_lb' => 'Topics',                '_cl' => 'topic_desc',             '_fl' => 'LS'),
		array ('_lb' => 'Summary',               '_cl' => 'summary',                '_fl' => 'LSENX', '_in' => 'textarea', '_v' => 'E', '_class' => 'summary'),
		array ('_lb' => 'Description',           '_cl' => 'description',            '_fl' => 'ENX',   '_in' => 'textarea', '_v' => 'E', '_class' => 'description'),
		array ('_lb' => 'Exclusions',            '_cl' => 'exclusions',             '_fl' => 'ENX',   '_in' => 'textarea', '_v' => 'E'),
		array ('_lb' => 'How To Apply',          '_cl' => 'howtoapply_desc',        '_fl' => 'LS'),
		array ('_lb' => 'How To Apply',          '_cl' => 'howtoapply_id',          '_fl' => 'ENX',   '_in' => 'select|,-|select id, description from how_to_apply', '_v' => 'E'),
		array ('_lb' => 'Grants Given',          '_cl' => 'grants_given',           '_fl' => 'LSENX', '_in' => 'select|0..30..1'),
		array ('_lb' => 'Year',                  '_cl' => 'year',                   '_fl' => 'LSENX', '_in' => 'select|2010..2060..1'),
		array ('_lb' => 'Grant Range Id',        '_cl' => 'grant_range_id',         '_fl' => 'ENX',   '_in' => 'select|,-|select id, description from grant_range'),
		array ('_lb' => 'Contact Title',         '_cl' => 'contact_title',          '_fl' => 'ENX',   '_in' => 'select|,-|select title from titles order by title'),
		array ('_lb' => 'Contact Name',          '_cl' => 'contact_name',           '_fl' => 'LSENX', '_in' => 'text'),
		array ('_lb' => 'Contact Address 1',     '_cl' => 'contact_addresss1',      '_fl' => 'ENX',   '_in' => 'text'),
		array ('_lb' => 'Contact Address 2',     '_cl' => 'contact_addresss2',      '_fl' => 'ENX',   '_in' => 'text'),
		array ('_lb' => 'Contact Address 3',     '_cl' => 'contact_addresss3',      '_fl' => 'ENX',   '_in' => 'text'),
		array ('_lb' => 'Contact City',          '_cl' => 'contact_city',           '_fl' => 'ENX',   '_in' => 'text'),
		array ('_lb' => 'Contact Postcode',      '_cl' => 'contact_postcode',       '_fl' => 'ENX',   '_in' => 'text'),
		array ('_lb' => 'Contact Country',       '_cl' => 'contact_country',        '_fl' => 'ENX',   '_in' => 'text'),
		array ('_lb' => 'Contact Telephone',     '_cl' => 'contact_telephone',      '_fl' => 'ENX',   '_in' => 'text'),
		array ('_lb' => 'Contact Email',         '_cl' => 'contact_email',          '_fl' => 'ENX',   '_in' => 'text', '_class' => 'contact_email'),
		array ('_lb' => 'Contact External URL',  '_cl' => 'contact_external_url',   '_fl' => 'ENX',   '_in' => 'text', '_class' => 'url'),
		array ('_lb' => 'Charity Commision URL', '_cl' => 'charity_commission_url', '_fl' => 'ENX',   '_in' => 'text', '_class' => 'url'),
		array ('_lb' => 'Search',                '_cl' => 'name',                   '_fl' => 'F',     '_in' => 'text', '_filter_sql' => "(name like '%{value}%' or summary like '%{value}%' or contact_name like '%{value}%')"),
		array ('_lb' => 'Topic',                 '_cl' => 'filter',                 '_fl' => 'F',     '_in' => 'select|,All Topics|select description from topic order by description', '_filter_sql' => "topic_desc like '%{value}%'"),
	),
	'_pagination' => array (
		'_rows_per_page' => 25,
	    '_row_counts' => array (2, 10, 25, 100, 500)
	),
	'_delete_sql' => array ("delete from scheme_topic where scheme_id = '{name}'")
);

doBat ($batDef, $dbh);

mysql_close($dbh);		// close database connection
?>
</body>
</html>