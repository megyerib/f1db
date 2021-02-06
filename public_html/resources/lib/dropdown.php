<?php
$DROPDOWNS = array();
class dropdown {
    public $name;
    public $list;

    function echo() {
        echo "<select name="$this->name">\n";
            foreach ($list as $value => $text)
            echo "\t<option value='$value'>$text</option>\n";
        echo "</select>\n";

        // JQuery-s kiválasztó script
        echo '<script>$("#'.$list_id.'").val("'.$chosen.'");</script>';
    }
}
?>
