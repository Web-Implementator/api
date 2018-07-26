<?php 

class mainClass {
    
	public function date_diff($date1, $date2)
	{
		$diff = strtotime($date1) - strtotime($date2);
		return round($diff/60);
	}
}