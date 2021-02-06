<?php
class driver {
    public $no;
    public $id;
    public $name;
    public $nat; // Nemzet rövidítés
    public $nation;

    // Olvasás adatbázisból
    function fillFromRow($row) {
        $this->no     = $row['driverNo'];
        $this->id     = $row['driverID'];
        $this->name   = $row['driverName'];
        $this->nat    = $row['countryID'];
        $this->nation = $row['countryName'];
    }

    function fillFromID($getID = this.id) {
        global $rdb;
        $result = $rdb->query(
            "SELECT driverNo, driverID, driverName, countryID, countryName
             FROM driver
             JOIN country
             ON driver.driverNation = country.countryNo
             WHERE driverID = '$getID'");
        if (!$result) return false;
        $row    = $result->fetch_assoc();
        $this->fillFromRow($row);
        return true;
    }

    function link() {
        return flag($this->nat, $this->nation).a('driver', $this->name, $this->id);
    }

    /* Rövidített név
       Valószínűleg csak egyszer és csak itt lesz ilyen függvény hívva, ezért
       sem külön változó, sem hívásellenőrzés nem kell. */

    function shortA() {
        return flag($this->nat, $this->nation).a('driver', $this->shortName(), $this->id);
    }

    function shortName() {
        return preg_replace("/([A-Z]{1})[a-z]+\s/", "\\1 ", $this->name);
    }
}
 ?>
