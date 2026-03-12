<!DOCTYPE html>
<html>
<head>
	<style>
		table {
		  font-family: arial, sans-serif;
		  border-collapse: collapse;
		  width: 100%;
		}

		td, th {
		  border: 1px solid #dddddd;
		  text-align: left;
		  padding: 8px;
		}
	</style>
</head>


<body>




<?php

		global $wpdb;
		global $table_prefix;

		$table = $table_prefix.'form_data';
		$sql="select * from $table ";
		$result = $wpdb->get_results($sql);
		//print_r($result);


?>

<h1> Display Data From The Database Table</h1>
<table border="1" >
	<tbody bgcolor="orange">
	<tr>
		<th>ID</th>
		<th>Name</th>
	</tr>
	</tbody>
	<?php
	foreach ($result as $list) {
		?>
		<tr>
			<td><?php echo $list->id; ?></td>
			<td><?php echo $list->name; ?></td>
		</tr>
	<?php
	 }


	?>

</table>
</body>
</html>