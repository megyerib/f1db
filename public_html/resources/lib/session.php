<?php
class session {
    public $no;
    public $event;
    public $type;
    public $num;

    public $displayName;

    public $results = array();

    function fillFromRow($row) {
        $this->no    = $row['sessionNo'];
        $this->event = $row['sessionEvent'];
        $this->type  = $row['sessionType'];
        $this->num   = $row['sessionNum'];

        $this->displayName = ucfirst($this->type).($this->num?" $this->num":"");
    }

    function addToResults($row) { // Ezt meg lehetne csinálni minden típushoz külön
        $entry = new entry;
        $entry->fillFromRow($row);
        $result = array(
            'entry' => $entry,
            'status'=> $row['resultStatus'],
            'start' => $row['resultStart'],
            'finish'=> $row['resultFinish'],
            'laps'  => $row['resultLaps'],
            'time'  => $row['resultTime'],
            'note'  => $row['resultNote'],
            'score' => $row['resultScore']
        );
        array_push($this->results, $result);
    }

    function getResults($no) {

    }
}
?>
