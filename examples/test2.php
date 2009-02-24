<?php

 $init = memory_get_usage();

 $i = 0;

	while($i <= 500){
		$t = create_function("", "return true;");
		unset($t);
		$i++;
	}
	
	$first = memory_get_usage();

 	$i = 0;

	while($i <= 500){
		create_function("", "return true;");
		$i++;
	}
	$last = memory_get_usage();
?>

	
	<?php
		$f = $first - $init;
		echo number_format($f);
		echo "<br />";
		echo number_format($last - $f);
	?>