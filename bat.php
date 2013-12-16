<?php

/*
===================================================================================================
BAT - Bob's Awesome Tables (for PHP)
===================================================================================================

    Author: Bob Marks
    Email:  marksie531@yahoo.com

    Description:

        Simple little PHP library for creating list / edit tables in PHP using a simple
        configuration.

        Adheres to the 80/20 rule i.e. should handle 80% of cases, for the more bespoke 20% of
        cases then it's better to create your own or update the source code here.

        This library handles: -

          1) Two different views of a database table (a) list (b) edit
          2) List view supports server side pagination, column sorting, custom multi-join queries
          3) List view supports
          4) List view supports exporting to excel, pdf, html

        Check the documentation for more information.

===================================================================================================
*/

// Show table grid function
function doBat ($batDef, $dbh) {

  // Set action from 'bat' attribute
  $action = "list";
  if (isset ($_GET['bat'])) {
    $action = $_GET['bat'];
  }
  else if (isset ($_POST['bat'])) {
    $action = $_POST['bat'];
  }

  // Actions
  if ($action == 'delete') {
    deleteRow ($batDef, $dbh, $_GET);
  }
  else if ($action == 'update') {
    updateRow ($batDef, $dbh, $_POST);
  }

  // Display view
  if ($action == 'edit') {
    echo showBatEdit ($batDef, $dbh);
  }
  else {
    echo showBatList ($batDef, $dbh);
  }
}

// Show table list
function showBatList ($batDef, $dbh) {
  $tableId = isset ($batDef['_list_id']) ? ' id="'.$batDef['_list_id'].'"' : '';
  $tableClass = ' class="'.(isset ($batDef['_list_class']) ? $batDef['_list_class'] : 'list_table').'"';

  $html = "<table$tableId$tableClass>
    <thead><tr>";

  // Action columns
  if (isset ($batDef['_can_edit']) && $batDef['_can_edit']) {
  	$html .= '<td>Edit</td>';
  }
  if (isset ($batDef['_can_delete']) && $batDef['_can_delete']) {
    $html .= '<td>Del</td>';
  }
  if (isset ($batDef['_can_print']) && $batDef['_can_print']) {
    $html .= '<td>Prt</td>';
  }
  if (isset ($batDef['_can_excel']) && $batDef['_can_excel']) {
    $html .= '<td>Exl</td>';
  }
  if (isset ($batDef['_can_pdf']) && $batDef['_can_pdf']) {
    $html .= '<td>Pdf</td>';
  }

  // Display columns
  $cols = $batDef ['_cols'];
  for ($i = 0; $i < count ($cols); $i++) {
    $col = $cols[$i];
    $flags = $cols[$i]['_flags'];
    $label = $cols[$i]['_lb'];
    if (strpos($flags, 'L') !== false) {
      $colClass = isset ($col['_class']) ? ' class="'.$col['_class'].'" ' : '';
      $html .= "<td$colClass>$label</td>";
    }
  }
  $html .= '</tr></thead>';

  $result = mysql_query ($batDef['_db_list_sql']);
  $num = mysql_numrows ($result);
  while ($row = mysql_fetch_assoc($result)) {
    $html .= '<tr>';
    $params = getPkeyParams($row, $batDef);

    // Do actions
    $action = $batDef['_action'];
    if (isset ($batDef['_can_edit']) && $batDef['_can_edit']) {
      $html .= '<td><a href="'.$action.'?bat=edit'.$params.'"><img src="images/bat/edit.gif"/></a></td>';
    }
	if (isset ($batDef['_can_delete']) && $batDef['_can_delete']) {
	  $deleteJs = '';
	  if (isset($batDef['_list_delete']) && $batDef['_list_delete'] != -1) {
	    $ld = $batDef['_list_delete'];
	    $column = isset ($cols[$ld]['_pkc']) ? $cols[$ld]['_pkc'] : $cols[$ld]['_col'];
        $value = $row [$column];
	    $deleteJs = ' onclick="return confirm(\'Are you sure you want do delete [ '.$value.' ]\')"';
	  }
	  $html .= '<td><a href="'.$action.'?bat=delete'.$params.'" '.$deleteJs.'><img src="images/bat/delete.gif"/></a></td>';
	}
	if (isset ($batDef['_can_print']) && $batDef['_can_print']) {
	  $html .= '<td><a href="'.$action.'?bat=print'.$params.'"><img src="images/bat/print.png"/></a></td>';
	}
	if (isset ($batDef['_can_excel']) && $batDef['_can_excel']) {
	  $html .= '<td><a href="'.$action.'?bat=excel'.$params.'"><img src="images/bat/excel.png"/></a></td>';
	}
	if (isset ($batDef['_can_pdf']) && $batDef['_can_pdf']) {
	  $html .= '<td><a href="'.$action.'?bat=pdf'.$params.'"><img src="images/bat/pdf.png"/></a></td>';
	}

    // Iterate over table columns
    for ($i = 0; $i < count ($batDef ['_cols']); $i++) {
      $flags = $cols[$i]['_flags'];

      if (strpos($flags, 'L') !== false) {
        $col = $cols[$i];
        $colClass = isset ($col['_class']) ? ' class="'.$col['_class'].'" ' : '';
        $html .= "<td$colClass>";

        if (isset ($col['_pkc'])) {
          $html .= $row[$col['_pkc']];
        }
        else if (isset ($col['_col'])) {
          $html .= $row[$col['_col']];
        }
        $html .='</td>';
      }
    }
    $html .= '</tr>';
  }
  $html .= '</table>';

  return $html;
}

// Show table edit screen
function showBatEdit ($batDef, $dbh) {
  // HTML attributes
  $tableId = isset ($batDef['_edit_id']) ? ' id="'.$batDef['_edit_id'].'"' : '';
  $tableClass = ' class="'.(isset ($batDef['_edit_class']) ? $batDef['_edit_class'] : 'edit_table').'"';
  $title = 'Edit ';

  // Run database query
  $rowData = getRowData ($_GET, $batDef);

  // Start creating HTML
  $html = '<form name="cancel" action="'.$batDef['_action'].'" method="GET"></form>
    <form name="edit" action="'.$batDef['_action'].'" method="POST">
    <input type="hidden" name="bat" value="update"/>'.getPkeyHiddenInputs($batDef, $rowData).'
    <table'.$tableId.$tableClass.'>
    <table id="'.$tableId.'">';

  // Header
  $html .= '<thead><tr><td colspan="2">'.$title.'</td></tr></thead>';

  // Body
  $html .= '<tbody>';
  $cols = $batDef ['_cols'];
  for ($i = 0; $i < count ($cols); $i++) {
    $col = $cols[$i];
    $flags = $col['_flags'];
    $label = $col['_lb'];
    if (strpos($flags, 'E') !== false || strpos($flags, 'R') !== false) {

      $column = isset ($cols[$i]['_pkc']) ? $cols[$i]['_pkc'] : $cols[$i]['_col'];
      $value = $rowData [strtolower($column)];

      // Create default input (textfield)
      $inputHtml = '<input type="text" name="'.$i.'"'.(strpos($flags, 'R') !== false ? 'disabled="disabled"' : '').' value="'.$value.'" />';

      // Check optinal "_input" field
      if (isset($col['_input'])) {
        $input = explode("|", $col['_input']);
        $numOfInputs = count ($input);

        // 1) Text field
		if ($input [0] == 'text') {
		  $dims = explode("|", $col['_input']);
		  $c = count ($dims);
		  if ($c == 1) {

		  }
		  else if ($c == 2) {

		  }
		}
        // 2) Combo box
        else if ($input [0] == 'combo_sql' && $numOfInputs == 2) {
          $inputHtml = '<select name="'.$i.'">';
          $result = mysql_query ($input [1]);
		  while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		    $s = $row[0] == $value ? ' selected="selected"' : '';
            $inputHtml .= '<option value="'.$row[0].'"'.$s.'>'.$row[1].'</option>';
		  }
          $inputHtml .= '</select>';
        }
      }

      $html .= '<tr>';
      $html .= '<td>'.$label.'</td>';
      $html .= '<td>'.$inputHtml.'</td>';
      $html .= '</tr>';
    }
  }

  $html .= '<tfoot><tr><td colspan="2">
  <input type="button" value="Cancel" onclick="javascript:document.cancel.submit();" />
  <input type="submit" value="OK" />
  </td></tr></tfoot>';

  // Footer
  $html .= '</table></form>';

  return $html;
}

// Delete row
function deleteRow ($batDef, $dbh) {
  $sql = "DELETE FROM ".$batDef["_db_table"]." WHERE ".getPkeySql($batDef);
  updateDb ($sql, $dbh);
}

// Update row
function updateRow ($batDef, $dbh) {
  $updateSql = 'UPDATE '.$batDef["_db_table"];
  $setSql = ' SET ';
  $whereSql = ' WHERE';
  $cols = $batDef ['_cols'];

  // Create update cols
  $sep1 = ' ';
  $sep2 = ' ';
  for ($i = 0; $i < count ($cols); $i++) {
    $col = $cols[$i];
    $flags = $col['_flags'];

    // Create SET part
    if (strpos($flags, 'E') !== false) {
      $column = isset ($cols[$i]['_pkc']) ? $cols[$i]['_pkc'] : $cols[$i]['_col'];
      $value = mysql_real_escape_string($_POST [$i]);

      // Append to update SQL
      $setSql .= "$sep1$column = '$value'";
      $sep1 = ", ";
    }

    // Create WHERE condition
    if (isset($col['_pkc'])) {
      $pKey = $col['_pkc'];
      $value = mysql_real_escape_string($_POST ["pk_$i"]);
      $whereSql .= "$sep2$pKey = '$value'";
      $sep2 = " AND ";
    }
  }
  $sql = $updateSql.$setSql.$whereSql;
  echo updateDb ($sql, $dbh);
}

// Update row
function updateDb ($sql, $dbh) {
  if (!mysql_query ($sql, $dbh)) {
    return mysql_error();
  }
  return "Success";
}

// Execute database query
function getResult ($sql) {
  $result = mysql_query ($sql);
  return mysql_fetch_assoc($result);
}

// Return row data
function getRowData ($batDef) {
  $sql = "SELECT * FROM ".$batDef['_db_table']." WHERE ".getPkeySql ($batDef);
  return getResult ($sql);
}

// Return primary key SQL
function getPkeySql ($batDef) {
  $sql = '';
  $cols = $batDef ['_cols'];
  $sep = '';
  for ($i = 0; $i < count ($cols); $i++) {
    $col = $cols[$i];
    if (isset($col['_pkc']) && isset($_GET[$i])) {
      $val = $_GET[$i];
      $sql .= $sep.$col['_pkc']." = '$val'";
      $sep = " AND ";
    }
  }
  return $sql;
}

// Return primary key parameters (using position in $batDef) from database row
function getPkeyParams ($rowData, $batDef) {
  $params = '';
  $cols = $batDef ['_cols'];
  $sep = '';
  for ($i = 0; $i < count ($cols); $i++) {
    $col = $cols[$i];
    if (isset($col['_pkc'])) {
      $pKey = $col['_pkc'];
      if (isset($rowData[$pKey])) {
        $val = $rowData[$pKey];
        $params .= "&$i=$val";
      }
    }
  }
  return $params;
}

// Return primary key as input items of type hidden (using position in $batDef) from database row
function getPkeyHiddenInputs ($batDef, $rowData) {
  $hidden = '';
  $cols = $batDef ['_cols'];
  $sep = '';
  for ($i = 0; $i < count ($cols); $i++) {
    $col = $cols[$i];
    if (isset($col['_pkc'])) {
      $pKey = $col['_pkc'];
      if (isset($rowData[strtolower($pKey)])) {
        $val = $rowData[strtolower($pKey)];
        $hidden .= '<input type="hidden" name="pk_'.$i.'" value="'.$val.'" />';
      }
    }
  }
  return $hidden;
}

?>