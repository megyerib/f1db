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

    function addToResults(&$entry, $row) { // Ezt meg lehetne csinálni minden típushoz külön
        if (!isset($this->results[$row['resultEntry']])) {
            $result = new result;
            $result->fillFromRow($row);
            $result->entry = $entry;
            $this->results[$row['resultEntry']] = $result;
        }
        else {
            array_push($this->results[$row['resultEntry']]->time, $row['resultTime']); // Időmérő...
        }
    }

    function getResults($no) {

    }
}

class result {
    public $entry;

    public $status;
    public $start;
    public $finish;
    public $laps;
    public $time = array(); // Kösz, időmérő
    public $note;
    public $score;

    function fillFromRow($row) {
        $this->status = $row['resultStatus'];
        $this->start  = $row['resultStart'];
        $this->finish = $row['resultFinish'];
        $this->laps   = $row['resultLaps'];
        array_push($this->time, $row['resultTime']);
        $this->note   = $row['resultNote'];
        $this->score  = $row['resultScore'];
    }
}

class qualifying extends session {
    function table() {
        if (count(array_values($this->results)[0]->time) == 3)
            $this->table3();
        else
            $this->table1();
    }

    function table3() {
        //varDump($this->results);
        echo "<table border='1'>";

        echo "<tr>
            <th>#</th>
            <th>Driver</th>
            <th>Q1</th>
            <th>Q2</th>
            <th>Q3</th>
            <th>Laps</th>
        </tr>";

        foreach ($this->results as $row) {
            echo "<tr>";
            echo "<td>".$row->finish."</td>";
            echo "<td>".$row->entry->driver->link()."</td>";
            $times = array('', '', ''); $i = 0;
            foreach ($row->time as $time) {
                $times[$i] = $time > 0 ? sec2time($time) : "No time";
                $i++;
            }
            foreach ($times as $time) {
                echo "<td>".$time."</td>";
            }
            echo "<td>".($row->laps ? $row->laps : '')."</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    function table1() {
        //varDump($this->results);
        echo "<table border='1'>";

        echo "<tr>
            <th>#</th>
            <th>Driver</th>
            <th>Time</th>
            <th>Laps</th>
        </tr>";

        foreach ($this->results as $row) {
            echo "<tr>";
            echo "<td>".$row->finish."</td>";
            echo "<td>".$row->entry->driver->link()."</td>";
            echo "<td>".($row->time[0] > 0 ? sec2time($row->time[0]) : "No time")."</td>";
            echo "<td>".($row->laps ? $row->laps : '')."</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

class race extends session {
    function table() {
        //varDump($this->results);
        echo "<table border='1'>";

        echo "<tr>
            <th>#</th>
            <th>Car#</th>
            <th>Driver</th>
            <th>Constructor</th>
            <th>Tyre</th>
            <th>Laps</th>
            <th>Time</th>
        </tr>";

        $firstTime = 0;
        foreach ($this->results as $row) {
            echo "<tr>";
                // Helyezés
                if ($row->status == 'c')
                    $place = $row->finish;
                else
                    $place = $row->status;
                echo "<td>".$place."</td>";

                echo "<td>".$row->entry->carNo."</td>";
                echo "<td>".$row->entry->driver->link()."</td>";
                echo "<td>".$row->entry->consLink()."</td>";
                echo "<td>".$row->entry->tyre->link()."</td>";

                echo "<td>".($row->laps ? $row->laps : '')."</td>";

                // Idő
                if ($row->finish == 1) {
                    $time = sec2timeHr($row->time[0]);
                    $firstTime = $row->time[0];
                }
                else if ($row->time[0] > 0)
                    $time = "+".sec2timeNoMin($row->time[0] - $firstTime);
                else
                    $time = $row->note;

                echo "<td>".$time."</td>";
            echo "</tr>";
        }
        echo "</table>";
    }


    /*
    ["status"]=>
        string(3) "ret"
        ["start"]=>
        string(2) "11"
        ["finish"]=>
        string(2) "24"
        ["laps"]=>
        string(1) "0"
        ["time"]=>
        array(1) {
          [0]=>
          string(5) "0.000"
        }
        ["note"]=>
        string(10) "front wing"
        ["score"]=>
        string(4) "0.00"
    */
}

class practice extends session {
    function table() {
        //varDump($this->results);
        echo "<table border='1'>";

        echo "<tr>
            <th>#</th>
            <th>Driver</th>
            <th>Time</th>
            <th>Laps</th>
        </tr>";

        foreach ($this->results as $row) {
            echo "<tr>";
            echo "<td>".$row->finish."</td>";
            echo "<td>".$row->entry->driver->link()."</td>";
            echo "<td>".($row->time[0] > 0 ? sec2time($row->time[0]) : "No time")."</td>";
            echo "<td>".($row->laps ? $row->laps : '')."</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}
?>
