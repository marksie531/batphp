<?php

// Constants
$defaultRowCount = 25;

/*  ===== TODO =====
3) VALIDATION
4) FILTERING
*/

// Show table grid function
function doBat ($batDef, $dbh) {

  // Do actions first
  $action = getAction ();
  if ($action == 'delete') {
    deleteRow ($batDef, $dbh, $_GET);
  }
  else if ($action == 'update') {
    updateRow ($batDef, $dbh, $_POST);
  }
  else if ($action == 'insert') {
    insertRow ($batDef, $dbh, $_POST);
  }

  // Display view
  $action = getAction ();
  if ($action == 'edit' || $action == 'new') {
    echo showBatEdit ($batDef, $dbh, ($action == 'new'));
  }
  else {
    echo showBatList ($batDef, $dbh);
  }
}

// Show table list
function showBatList ($batDef, $dbh) {

  // 1) Parameters
  $cols = $batDef ['_cols'];
  $action = $batDef ['_action'];
  $url = $action.'?bat=list';
  $defaultSort = isset ($batDef['_default_sort']) ? $batDef['_default_sort'] : -1;
  $defaultAsc = isset ($batDef['_default_asc']) ? $batDef['_default_asc'] : 1;
  $sort = isset ($_GET['sort']) ? $_GET['sort'] : $defaultSort;
  $asc = isset ($_GET['asc']) ? $_GET['asc'] : $defaultAsc;
  $isPaged = isset ($batDef['_pagination']);

  // 2) Create and execute List SQL query
  $sql = $batDef['_db_list_sql'];
  if ($sort != -1) {
    $col = isset ($cols[$sort]['_pkc']) ? $cols[$sort]['_pkc'] : $cols[$sort]['_col'];
    $sql .= ' ORDER BY '.$col.' '.($asc == 1 ? 'ASC' : 'DESC');
  }

  // ... apply pagination to SQL query
  if ($isPaged) {
    $pagination = $batDef['_pagination'];
    $rowsPerPage = getRowsPerPage ($batDef);
    $pageNum = isset($_GET['page']) ? $_GET['page'] : 0;
    $offset = $pageNum * $rowsPerPage;

    $sql .= " LIMIT $offset, $rowsPerPage";
  }

  debug ($batDef, $sql.';');
  $result = mysql_query ($sql);

  // 3) Create table headers
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

  // ... columns
  for ($i = 0; $i < count ($cols); $i++) {
    $col = $cols[$i];
    $flags = $cols[$i]['_flags'];
    $label = $cols[$i]['_lb'];
    if (strpos($flags, 'L') !== false) {
      $colClass = isset ($col['_class']) ? ' class="'.$col['_class'].'" ' : '';
      $header = '';
      if (strpos($flags, 'S') !== false) {
        $curAsc = $i == $sort ? -$asc : $asc;
        $header .= '<a href="'.$url.'&sort='.$i.'&asc='.$curAsc.'">'.$label.'</a>';
        if ($sort == $i) {
          $header .= ($asc == -1) ? ' <img src="images/bat/navdown.gif" alt="Sort" />' : ' <img src="images/bat/navup.gif" alt="Sort" />';
        }
      }
      else {
        $header = $label;
      }

      $html .= "<td$colClass>$header</td>";
    }
  }
  $html .= '</tr></thead>';

  // 4) Iterate through result set and create body
  $html .= '<tbody>';
  while ($row = mysql_fetch_assoc($result)) {
    $html .= '<tr>';
    $params = getPkeyParams($row, $batDef).
      (isset($_GET['sort']) ? '&sort='.$_GET['sort'] : '').
      (isset($_GET['asc']) ? '&asc='.$_GET['asc'] : '');

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
  $html .= '</tbody>';

  // 5) Display pagination footer if applicable
  if ($isPaged) {
    $pagination = $batDef ['_pagination'];
    $page = isset ($_GET['page']) ? $_GET['page'] : 0;
    $html .= '<form name="rows" action="'.$batDef['_action'].'">
    <tfoot><tr><td colspan="'.getColumns($batDef).'">';

    // Create "Display" section
    if (isset ($pagination['_row_counts'])) {
      $page = isset($_GET['page']) ? $_GET['page'] : 0;
      $rowsPerPage = getRowsPerPage ($batDef);
      $totalRows = getValue ($pagination['_db_count_sql']);
      $maxPage = ceil ($totalRows / $rowsPerPage);

      // Create pagination HTML
      $html .= '<strong>Display: </strong>
        <select name="rows" onchange="javascript:document.rows.submit()">';
      foreach ($pagination['_row_counts'] as $rowCount) {
        $s = $rowsPerPage == $rowCount ? ' selected="selected"' : '';
        $html .= '<option'.$s.'>'.$rowCount.'</option>';
      }
      $html .= '</select> <span>';

      $addHtml = "&rows=".$rowsPerPage;
      if ($page > 0) {
        $html .= ' <a href="'.$action.'?page=0'.$addHtml.'"><img alt="First" title="First" src="images/bat/navfirst.gif" width="16" height="16" /></a>';
	  	$html .= ' <a href="'.$action.'?page='.($page - 1).$addHtml.'"><img alt="Previous" title="Previous" src="images/bat/navprev.gif" width="16" height="16" /></a>';
      }
      $html .= ' </span><span><strong>'.($page + 1).'</strong> of <strong>'.($maxPage).'</strong></span> <span>';
      if ($page + 1 < $maxPage) {
	    $html .= ' <a href="'.$action.'?page='.($page + 1).$addHtml.'"><img alt="Next" title="Next" src="images/bat/navnext.gif" width="16" height="16" /></a>';
	    $html .= ' <a href="'.$action.'?page='.($maxPage - 1).$addHtml.'"><img alt="Last" title="Last" src="images/bat/navlast.gif" width="16" height="16" /></a>';
      }
      $html .= '</span>';
    }
    $html .= '</td></tr></tfoot></form>';
  }

  $html .= '</table>';

  return $html;
}

// Show table edit screen
function showBatEdit ($batDef, $dbh, $isNew) {
  // HTML attributes
  $tableId = isset ($batDef['_edit_id']) ? ' id="'.$batDef['_edit_id'].'"' : '';
  $tableClass = ' class="'.(isset ($batDef['_edit_class']) ? $batDef['_edit_class'] : 'edit_table').'"';
  $title = $isNew ? 'New' : 'Edit';
  $action = $isNew ? 'insert' : 'update';

  // Run database query
  if (!$isNew) {
  	$rowData = getRowData ($batDef);
  }

  // Start creating HTML
  $html = '<form name="cancel" action="'.$batDef['_action'].'" method="GET"></form>
    <form name="edit" action="'.$batDef['_action'].'" method="POST">
    <input type="hidden" name="bat" value="'.$action.'"/>';
  if (!$isNew) {
    $html .= getPkeyHiddenInputs($batDef, $rowData);
  }
  $html .= "<table$tableId$tableClass>";

  // Header
  $html .= '<thead><tr><td colspan="2">'.$title.'</td></tr></thead>';

  // Body
  $html .= '<tbody>';
  $cols = $batDef ['_cols'];
  for ($i = 0; $i < count ($cols); $i++) {
    $col = $cols[$i];
    $flags = $col['_flags'];
    $label = $col['_lb'];

    $display = $isNew ? strpos($flags, 'N') !== false : strpos($flags, 'E') !== false || strpos($flags, 'R') !== false;
    if ($display) {

      $column = isset ($cols[$i]['_pkc']) ? $cols[$i]['_pkc'] : $cols[$i]['_col'];
      $readOnly = !$isNew && strpos($flags, 'R') !== false;
      $value = '';

      // Set value
      if ($isNew) {
        if (isset ($_POST[$i])) {
          $value = $_POST[$i];
        }
      } else {
        $value = $rowData [strtolower($column)];
      }

      // $value = !$isNew ? $rowData [strtolower($column)] : $_POST[$i];

      // Create default input (textfield)
      $inputHtml = '<input type="text" name="'.$i.'"'.($readOnly ? 'disabled="disabled"' : '').' value="'.$value.'" />';

      // Check optinal "_input" field
      if (isset($col['_input'])) {
        $input = explode("|", $col['_input']);
        $numOfInputs = count ($input);

        // 1) Text field
		if ($input [0] == 'text') {
		  // TODO
		}
        // 2) Combo box
        else if ($input [0] == 'combo_sql' && $numOfInputs == 2) {
          $inputHtml = '<select name="'.$i.'"'.($readOnly ? 'disabled="disabled"' : '').'>';
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
  debug ($batDef, $sql.';');
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
  debug ($batDef, $sql.';');
  if (!mysql_query ($sql, $dbh)) {
    $_GET['bat'] = 'new';
    error (mysql_error());
  }
  else {
    success ();
  }
}

// Update row
function insertRow ($batDef, $dbh) {
  $insertSql = 'INSERT INTO '.$batDef["_db_table"].' ';
  $columnsSql = '(';
  $valuesSql = ') VALUES (';
  $cols = $batDef ['_cols'];

  // Create update cols
  $sep = '';
  for ($i = 0; $i < count ($cols); $i++) {
    $col = $cols[$i];
    $flags = $col['_flags'];

    // Create SET part
    if (strpos($flags, 'N') !== false) {
      $column = isset ($cols[$i]['_pkc']) ? $cols[$i]['_pkc'] : $cols[$i]['_col'];
      $value = mysql_real_escape_string($_POST [$i]);

      // Append to update SQL
      $columnsSql .= "$sep$column";
      $valuesSql .= "$sep'$value'";
      $sep = ", ";
    }
  }
  $valuesSql .= ")";

  $sql = $insertSql.$columnsSql.$valuesSql;
  debug ($batDef, $sql.';');
  if (!mysql_query ($sql, $dbh)) {
    $_GET['bat'] = 'new';
    error (mysql_error());
  }
  else {
    success ("New item inserted successfully");
  }
}

// Update database
function updateDb ($sql, $dbh) {
  if (!mysql_query ($sql, $dbh)) {
    return mysql_error();
  }
}

// Get single value
function getValue ($sql) {
  $result = mysql_query ($sql);
  $row = mysql_fetch_array($result, MYSQL_NUM);
  return $row[0];
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

// Return number of columns in the list table
function getColumns ($batDef) {
  $count = 0;
  if (isset ($batDef['_can_edit']) && $batDef['_can_edit']) {
    $count ++;
  }
  if (isset ($batDef['_can_delete']) && $batDef['_can_delete']) {
    $count ++;
  }
  if (isset ($batDef['_can_print']) && $batDef['_can_print']) {
    $count ++;
  }
  if (isset ($batDef['_can_excel']) && $batDef['_can_excel']) {
    $count ++;
  }
  if (isset ($batDef['_can_pdf']) && $batDef['_can_pdf']) {
    $count ++;
  }

  // Iterate over table columns
  for ($i = 0; $i < count ($batDef ['_cols']); $i++) {
    $flags = $batDef['_cols'][$i]['_flags'];
    if (strpos($flags, 'L') !== false) {
      $count ++;
    }
  }
  return $count;
}

// Return rows per page
function getRowsPerPage ($batDef) {
  $defaultRowsPerPage = isset ($batDef['_pagination']['_rows_per_page']) ? $batDef['_pagination']['_rows_per_page'] : 25;
  $rowsPerPage = isset ($_GET['rows']) ? $_GET['rows'] : $defaultRowsPerPage;
  return $rowsPerPage;
}

// Get action
function getAction () {
  $action = "list";
  if (isset ($_GET['bat'])) {
    $action = $_GET['bat'];
  }
  else if (isset ($_POST['bat'])) {
    $action = $_POST['bat'];
  }
  return $action;
}

// Display success message
function success ($message) {
  echo '<p class="success">'.$message.'</p>';
}

// Display error message
function error ($message) {
  echo '<p class="error">'.$message.'</p>';
}

// Display debug message
function debug ($batDef, $message) {
  if (isset ($batDef['_debug_sql']) && $batDef['_debug_sql']) {
    echo '<div id="debug">'.$message.'<div>';
  }
}

?>