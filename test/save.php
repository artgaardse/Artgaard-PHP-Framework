<?php

	include("../model.inc.php");

	class Bil extends Model
	{
		public $brand = "";
		public $year = 0;
		public $price = 0;
	}

	if(isset($_POST['save']))
	{
	
		
		$b = new Bil();
		
		$b->brand = $_POST['brand'];
		$b->year = (int) $_POST['year'];
		$b->price = (int) $_POST['price'];
		
		$b->save();
		
		
	}
?>
	<form action="save.php" method="post">
		Brand <Input type="text" name="brand"><br/>
		Year <Input type="text" name="year"><br/>
		Price <Input type="text" name="price"><br/>
		<Input type="submit" name="save"><br/>
	</form>
	
<?php


	$ob = Bil::getAllObjects("");

	foreach($ob as $o)
	{
		echo $o->brand." - ".$o->year." - ".$o->price."<br/>";
	}
?>