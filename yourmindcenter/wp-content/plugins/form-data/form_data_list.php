<style>

		label {
		  font-family: arial, sans-serif;
		  border-collapse: collapse;
		  width: 50%;
		  background-color: orange; 
		  border: 1px solid #dddddd;
		  text-align: left;
		  padding: 10px;
		  margin-right: 5px;
		}

		input{
		  border: 1px solid #dddddd;
		  padding: 10px;
		}
		form{
			border: 1;
			background: pink;
			background: pink;
		    width: 50%;
		    border: 1px solid #020b0e;
	    
		}
		button{
		   font-family: arial, sans-serif;
		   padding: 8px;
		   padding: 8px;
    	   width: 100%;
    	   background-color: #00bd00;

		}
	</style>

<?php
if($_POST)
{
	
	$fname = $_POST['fname'];
	$lname = $_POST['lname'];
	$phone  = $_POST['phone'];
		global $wpdb;
		global $table_prefix;

		$table = $table_prefix.'form_data';

		$sql="INSERT INTO $table (fname, lname, phone) VALUES ('$fname', '$lname', '$phone') ";
		$result = $wpdb->get_results($sql);

	}	
?>
<form method="post">
	 <h1>Register</h1>
<label>First Name: </label>
<input type="text" name="fname"> <br><br>
<label>Last Name:</label>
<input type="text" name="lname"> <br><br>
<label>Phone:</label>
<input type="text" name="phone"> <br><br>


<button type="submit"  name="btnInsert"> Submit</button> 
</form>


