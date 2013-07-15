<html>
<!--
/** * Copyright 2013 Microsoft Corporation 
	*  
	* Licensed under the Apache License, Version 2.0 (the "License"); 
	* you may not use this file except in compliance with the License. 
	* You may obtain a copy of the License at 
	* http://www.apache.org/licenses/LICENSE-2.0 
	*  
	* Unless required by applicable law or agreed to in writing, software 
	* distributed under the License is distributed on an "AS IS" BASIS, 
	* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 
	* See the License for the specific language governing permissions and 
	* limitations under the License. 
	*/
-->
<head>
	<title>My ToDo List</title>
	<style type="text/css">
	body { background-color: #fff; border-top: solid 10px #000;
		color: #333; font-size: .85em; margin: 20px; padding: 20px;
		font-family: "Segoe UI", Verdana, Helvetica, Sans-Serif;
	}

	h1, h2, h3 { color: #000; margin-bottom: 0; padding-bottom: 0; }

	h1 { font-size: 2em; }

	h2 { font-size: 1.75em; }

	h3 { font-size: 1.2em; }

	table { margin-top: 0.75em;}

	th { font-size: 1.2em; text-align: center; border: none 0px; padding-right: 15px; }

	td { padding: 0.25em 2em 0.25em 0em; border: 0 none; }
	</style>
</head>
<body>
	<h1>My ToDo List</h1>

<?php
	header('Cache-Control: no-cache');
	header('Pragma: no-cache');
	require_once "getitems.php";
	$items = getItems();
	if(!empty($items))
	{
		echo "<table border='1'>
				<tr>
					<th>Name</th>
					<th>Category</th>
					<th>Date</td>
					<th>Complete</th>
					<th>Mark Complete?</th>
					<th>Delete?</th>
				</tr>";
		foreach($items as $item)
		{
			echo 	"<tr>
						<td>".$item[1]."</td>
						<td>".$item[2]."</td>
						<td>".$item[3]."</td>";
							
			if($item[4] == 0)
			{
				echo "<td>No</td>";
				echo "<td><a href='markitemcomplete.php?id=".$item[0]."'>Mark complete</a></td>";
			}
			else
			{
				echo "<td>Yes</td>";
				echo "<td>N/A</td>";
			}
			echo "<td><a href='deleteitem.php?id=".$item[0]."'>Delete</a></td>";
			echo "</tr>";
		}
		
		echo "</table>";
	}
?>
	<hr/>
	<form action="additem.php" method="post">
		<table border="1">
			<tr>
				<td>Item Name: </td>
				<td><input name="itemname" type="text"/></td>
			</tr>
			<tr>
				<td>Item Category: </td>
				<td><input name="itemcategory" type="text"/></td>
			</tr>
			<tr>
				<td>Item Date (yyyy-mm-dd): </td>
				<td><input name="itemdate" type="text"/></td>
			</tr>
		</table>
		<input type="submit" value="Add item"/>
	</form>
</body>
</html>