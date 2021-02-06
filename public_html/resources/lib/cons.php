<?php
class cons {
    public $no;
    public $id;
    public $name;
    public $nat; // Nemzet rövidítés
    public $nation;

    // Olvasás adatbázisból
    function fillFromRow($row) {
        $this->no     = $row['consNo'];
        $this->id     = $row['consID'];
        $this->name   = $row['consName'];
        if (isset($row['countryID'])) {
            $this->nat    = $row['countryID'];
            $this->nation = $row['countryName'];
        }
    }

    function fillFromID($getID = this.id) {
        global $rdb;
        $result = $rdb->query(
            "SELECT consNo, consID, consName, countryID, countryName
             FROM cons
             JOIN country
             ON cons.driverNation = country.countryNo
             WHERE consID = '$getID'");
        $row    = $result->fetch_assoc();
        $this->fillFromRow($row);
    }

    function a() {
        return a('cons', $this->name, $this->id);
    }
}
?>
