<?php
class event {
    public $no;
    public $seriesID;
    public $seriesName;
    public $year;
    public $gp;
    public $gpID;

    function fillFromRow($row) {
        $this->no   = $row['eventNo'];
        $this->year = $row['eventYear'];
        $this->gpID = $row['countryID'];
        $this->gp   = $row['countryGP'];
        if (isset($row['seriesID'])) { // Nem mindig kell külön joinolni a táblát
            $this->seriesID   = $row['seriesID'];
            $this->seriesName = $row['seriesName'];
        }
    }

    function a() {
        $text = "$this->year $this->gp GP";
        return a('event', $text, $this->seriesID, $this->year, $this->gpID);
    }

    function fillFromData($series, $year, $gp) {
        global $rdb;

        $results = $rdb->query(
            "SELECT eventNo, eventYear, countryID, countryGP, seriesID, seriesName
             FROM event
             JOIN country
             ON eventGP = countryNo
             JOIN series
             ON eventSeries = seriesNo
             WHERE eventYear = $year
             AND seriesID = '$series'
             AND countryID = '$gp'"
        );
        if ($results->num_rows == 0) return false;
        $row = $results->fetch_assoc();
        $this->fillFromRow($row);
        return true;
    }
}
?>
