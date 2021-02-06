<?php
class entry {
	public $title = '';
	public $yr = '';
	public $mon = '';
	public $day = '';
	public $text = '';
	public $author = '';
	
	function display() {
		echo "<div class='entry'>".
		"<div class='date'><p>$this->yr<br>$this->mon</p><p>$this->day</p></div>".
		"<h1 class='title'>$this->title</h1>".
		"<p class='subtitle'>$this->author</p>".
		"$this->text</div>";
	}
}
?>