<?php
$conn = new mysqli("localhost", "id18864578_rubenstap", "fuHr&5J%4h7jReCI");
$sql = "";
$content_length = 0;

if ($_GET["language"] == "dut" || $_GET["language"] == "en") {
    if ($_GET["table"] == "simple" || $_GET["table"] == "set_region" ||
        $_GET["table"] == "set_prediction" || $_GET["table"] == "region" ||
        $_GET["table"] == "set_rainprediction" || $_GET["table"] == "prediction_categories" ||
        $_GET["table"] == "months" || $_GET["table"] == "day_numbers_date" ||
        $_GET["table"] == "rain_predictions") {
        $sql = "SELECT {$_GET["language"]} FROM id18864578_data.{$_GET["table"]} WHERE name =".' "'."{$_GET["name"]}".'"';
    }

    $result_query = $conn->query($sql);
    $sound = $result_query->fetch_assoc()[$_GET["language"]];
    $bytes_in_sound = strlen($sound);
    $bytes_in_sound_minus_one = $bytes_in_sound - 1;

    header("Content-type: audio/wav");
    header("Content-length: {$bytes_in_sound}");
    header("Content-range: bytes 0-{$bytes_in_sound_minus_one}/{$bytes_in_sound}");
    echo $sound;
}

?>