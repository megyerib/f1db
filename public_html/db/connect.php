<?php
$mysqli = new mysqli('localhost', 'root', '', 'f1');

class query{
    public $dbc;

    // Lekérdezés definiálása
    public $fields = array();
    public $from;
    public $join = array();

    // Egyes lapok

    function __construct($db = false) {
        $this->dbc = $db;
    }
    function connect($db) {
        $this->dbc = $db;
    }

    function queryHeader($string) { // SELECT ... FROM ... JOIN ...
        $string = explode(PHP_EOL, $string);
    }
}

$q = new query($mysqli);

$qh = "SELECT d.asd a, t.name as nme, tr.country ctry
FROM driver d
JOIN team t on t.no = d.no
left join tyre tr
ON (t.tyre = tr.no)";

$mysqli->close();
?>
