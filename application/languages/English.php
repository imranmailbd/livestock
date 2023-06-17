<?php
class English{
	public function index($index){
		$languageData = array(
		);
		if(array_key_exists($index, $languageData)){
			return $languageData[$index];
		}
		return false;
	}
}
?>