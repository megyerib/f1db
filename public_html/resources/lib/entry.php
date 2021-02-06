<?php
class entry {
    public $driver;
    public $chassisCons;
    public $engineCons;
    public $tyre;
    public $carNo;

    function fillFromRow($row) {
        $this->driver = new driver;
        $this->chassisCons = new cons;
        $this->engineCons = new cons;
        $this->tyre = new tyre;

        $this->driver->id     = $row['driverID'];
        $this->driver->name   = $row['driverName'];
        $this->driver->nat    = $row['countryID'];
        $this->driver->nation = $row['countryName'];

        // Chassis
        $this->chassisCons->id   = $row['chassisID'];
        $this->chassisCons->name = $row['chassisName'];

        // Engine
        $this->engineCons->id   = $row['engineID'];
        $this->engineCons->name = $row['engineName'];

        $this->tyre->id = $row['tyreID'];
        $this->carNo = $row['entryCarNo'];
    }

    function consLink() {
        if ($this->chassisCons->id != $this->engineCons->id)
            return $this->chassisCons->a()." - ".$this->engineCons->a();
        else
            return $this->chassisCons->a();
    }
}
?>
