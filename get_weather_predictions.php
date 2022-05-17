<?php
function get_previous_day($lat, $long, $start, $api_key) {
    $url = "http://history.openweathermap.org/data/2.5/history/city?lat=$lat&lon=$long&type=hour&start=$start&cnt=24&appid=$api_key";
    $json = file_get_contents($url);
    $obj = json_decode($json, $assoc = true);
    return $obj;
}

function get_climatic_forecast($lat, $long, $api_key) {
    $url = "https://pro.openweathermap.org/data/2.5/forecast/climate?lat=$lat&lon=$long&appid=$api_key";
    $json = file_get_contents($url);
    $obj = json_decode($json, $assoc = true);
    return $obj;
}

function get_historical_data($lat, $long, $api_key) {
    $cur_day_since_epoch = intdiv(time(), 86400);
    $rain_per_day = array();

    for ($day = $cur_day_since_epoch - 35; $day < $cur_day_since_epoch; $day++) {
        $hourlist = get_previous_day($lat, $long, $day * 86400 + 3600, $api_key)["list"];

        $raintoday = 0.0;
        foreach ($hourlist as $hourdata) {
            if (array_key_exists("rain", $hourdata)) {
                $raintoday += $hourdata["rain"]["1h"];
            }
        }

        array_push($rain_per_day, $raintoday);
    }

    return $rain_per_day;
}

function first_two_months() {
    $monthno = (((time() % 31556926) / 31556926) * 12);
    if ($monthno <= 2) {
        return true;
    } else {
        return false;
    }
}

function get_future_raindata($lat, $long, $api_key) {
    $rain_per_day = array();
    $future_raindata = get_climatic_forecast($lat, $long, $api_key)["list"];

    foreach ($future_raindata as $future_day) {
        if (array_key_exists("rain", $future_day)) {
            array_push($rain_per_day, $future_day["rain"]);
        } else {
            array_push($rain_per_day, 0.0);
        }
    }

    return $rain_per_day;
}

function does_rain_season_start($history_raindata, $next_raindata) {
    $history_biggest = max($history_raindata);
    $history_sum = array_sum($history_raindata);

    $no_wet_days = 0;
    foreach ($next_raindata as $next_rain_day) {
        if ($next_rain_day > 0.0) {
            $no_wet_days += 1;
        }
    }

    echo "In total $history_sum mm of rain in the past.<br>";
    echo "At most $history_biggest mm of rain in the past.<br>";
    echo "$no_wet_days days of rain in the future.<br>";

    if ($history_sum >= 40.0 && $history_biggest >= 16.0 && $no_wet_days >= 12) {
        return true;
    } else {
        return false;
    }
}

function determine_rainy_season_start($raindata, $n_history_vals) {
    for ($i = 5; $i < count($raindata) - 11; $i++) {
        $cur_window = array_slice($raindata, $i - 5);
        $cur_day = $i - $n_history_vals - 1;
        echo "Day $cur_day <br>";
        if (does_rain_season_start(array_slice($cur_window, 0, 5), array_slice($cur_window, 5)) == true) {
            return max($i - $n_history_vals - 1, -1);
        }
    }

    return -2;
}

ini_set("allow_url_fopen", 1);
$api_key = "5b1618c38d84876d161ee8776e011fc7";

// $dat = get_historical_data($lat, $long, $api_key);
// file_put_contents('historical_data.tmp', json_encode($dat));

// $prev_raindat = file_get_contents('historical_data.tmp');
// $prev_raindat = json_decode($prev_raindat);
// determine_rainy_season_start($dat, 5);
//
// $future_raindat = get_future_raindata($lat, $long, $api_key);
// $res = determine_rainy_season_start(array_merge($prev_raindat, $future_raindat), 35);
$conn = new mysqli("localhost", "debian-sys-maint", "aQxrH9qK0JAgaTHs");
$sql = "SELECT * FROM id18864578_data.predictions";
$result_query = $conn->query($sql);

$DB_PERMANENT_RAINSEASON_STARTED = -2;
$DB_PREDICTION_RAINSEASON_STARTED = -1;

$FUNC_RAINSEASON_NOT_PREDICTED = -2;
$FUNC_RAINSEASON_STARTED = -1;

while ($row = $result_query->fetch_assoc()) {
    if (first_two_months()) {
        $region = $row['region_name'];
        $conn->query("UPDATE id18864578_data.predictions SET rainy_season_prediction = -1 WHERE region_name = " . '"' . $region . '"');
    // If the value is -2, the rain season has already started, based on non-predictional data.
    // The estimate then doesn't need to be adjusted anymore.
    } else if ($row["rainy_season_prediction"] != $DB_PERMANENT_RAINSEASON_STARTED) {
        $historical_data = get_historical_data($row['latitude'], $row["longitude"], $api_key);
        $ret = determine_rainy_season_start($historical_data, 5);
        if ($ret != $FUNC_RAINSEASON_NOT_PREDICTED) {
            $conn->query("UPDATE id18864578_data.predictions SET rainy_season_prediction = -2 WHERE region_name = " . '"' . $region . '"');
        }
    }
}
?>
