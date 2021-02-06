<?php
class tyre {
    public $no;
    public $id;
    public $name;
    public $nat; // Nemzet rövidítés
    public $nation;

    // Olvasás adatbázisból
    function fillFromRow($row) {
        $this->no     = $row['tyreNo'];
        $this->id     = $row['tyreID'];
        $this->name   = $row['tyreName'];
        if (isset($row['countryID'])) {
            $this->nat    = $row['countryID'];
            $this->nation = $row['countryName'];
        }
    }
}
?>
