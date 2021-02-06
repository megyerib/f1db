<?php
class series {
    public $no;
    public $id;
    public $name;

    /*function __construct($input) {
        if (is_string($input))
            $this->fillFromID($input);
        else
            $this->fillFromRow($input);
    }*/

    // Olvasás adatbázisból
    function fillFromRow($row) {
        $this->no   = $row['seriesNo'];
        $this->id   = $row['seriesID'];
        $this->name = $row['seriesName'];
    }

    function fillFromID($getID = this.id) {
        global $rdb;
        $result = $rdb->query("SELECT * FROM series WHERE seriesID = '$getID'");
        if ($result->num_rows == 0) {
            $result->close();
            return false;
        }
        $row    = $result->fetch_assoc();
        $this->fillFromRow($row);
        $result->close();
        return true;
    }

    // Irás adatbázisba
    function insert() {
        global $rdbAdmin;
        $rdbAdmin->query(
            "INSERT INTO series(seriesID, seriesName)
             VALUES ('$this->id', '$this->name')"
        );
    }

    function update() {
        global $rdbAdmin;
        $rdbAdmin->query(
            "UPDATE series
             SET seriesID = '$this->id',
             seriesName = '$this->name'
             WHERE seriesNo = $this->no"
        );
    }

    function delete() {
        global $rdbAdmin;

        if (!isset($this->no))
            $this->no = -1;
        if (!isset($this->id))
            $this->id = '';

        $rdbAdmin->query(
            "DELETE FROM series
             WHERE seriesNo = $this->no
             OR seriesID = $this->id"
        );
    }

    // Link
    function a() {
        return a('seasons', $this->name, $this->id);
    }
}
?>
