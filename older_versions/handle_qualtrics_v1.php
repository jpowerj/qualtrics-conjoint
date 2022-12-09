<?php

///// PHP internal stuff, no need to change this
// (Hooray for PHP totally working like a normal language :|)
date_default_timezone_set('America/New_York');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', true);
ini_set('error_log',"/root/php_errors.log");
// Required for currency formatting
setlocale(LC_MONETARY, 'en_US.UTF-8');

/*
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // The request is using the GET method
    echo "NOTE: GET request received by Qualtrics server -- please set the Web Service call in Qualtrics to instead send a POST request.<br>";
}
*/

/*
$GLOBALS["characteristics"] = array(
    "jobtitle" => ["Cashier & Front End", "Sales Associate", "Cart Attendant & Janitorial",
        "Stocker, Backroom, & Receiving", "Fresh Food Associate", "Asset Protection",
        "Automotive", "Pharmacy", "Vision", "Department Manager", "Remodel Associate"],
    "hrs" => ["20 hours or less", "20-40 hours", "40 hours or more"],
    "controlhrs" => ["Yes", "No"],
    "friends" => ["None", "Some", "Many", "All"],
    "commute" => ["0-15 minutes", "15-30 minutes", "30-60 minutes", "More than 60 minutes"],
    "physical" => ["Yes","No"]
);
*/

$GLOBALS["characteristics"] = array(
    "wage" => ["NORM", 5, "$"],
    "commute" => ["0-15 minutes","16-30 minutes","More than 30 minutes"],
    "numhrs" => ["DISCRETE_UNIF", 3],
    "healthcare" => ["Yes", "No"],
    "lunch" => ["CONTINUOUS_UNIF", 5]
);
$num_chars = count($GLOBALS["characteristics"]);
$char_list = array_keys($GLOBALS["characteristics"]);

$GLOBALS["table_display"] = array(
    "wage" => "Hourly Wage (USD)",
    "commute" => "Daily Commute",
    "numhrs" => "Hours Per Week",
    "healthcare" => "Healthcare Provided?",
    "lunch" => "Lunch Break Duration"
);

$GLOBALS["defaults"] = array(
    "wage" => 15.00,
    "commute" => "16-30 minutes",
    "numhrs" => 40,
    "healthcare" => "No",
    "lunch" => 10.00
);

// This will be iteratively updated to contain the array we want to return to Qualtrics
$GLOBALS["r"] = array();

// User metadata
// Postal
/*
$user_postal = NULL;
if(isset($_POST['user_postal'])){
    $user_postal = $_POST['user_postal'];
}
*/
//error_log("Postal: " . $user_postal . "\n");

///// Debugging zone :3
//$post_str = var_export($_POST, true);
//error_log($post_str);
//$log_str = "Query String: " . $_SERVER['QUERY_STRING'] . "\n";
//$log_str = $log_str . "Entered Hours: " . $user_hours . "\n";
// Save it to a file real quick
//$filename = '/home/bitnami/query.txt';
//file_put_contents($filename, $log_str);

$GLOBALS["r"]["num_offers"] = 1;
if (isset($_POST["num_offers"])) {
    $GLOBALS["r"]["num_offers"] = $_POST["num_offers"];
}
// From the 10 characteristics we have (outside of wage and suprespect), chooses one
// uniformly at random
//$rand_char_index = array_rand($GLOBALS["characteristics"]);
//$GLOBALS['randomized_char'] = $GLOBALS["characteristics"][$rand_char_index];
// NOPE for now, just always randomizing commute time
//$GLOBALS["randomized_char"] = "CommuteTime";
// VERSION 2.0: Now *all* the vars are randomized
//$GLOBALS["randomized_chars"] = $GLOBALS["characteristics"];

function generateGaussian($mu, $sigma){
	   $u1 = mt_rand() / mt_getrandmax();
	   $u2 = mt_rand() / mt_getrandmax();
	   $z0 = sqrt(-2.0 * log($u1)) * cos(2 * pi() * $u2);
	   //z1 = sqrt(-2.0 * log(u1)) * sin(two_pi * u2);
	   return($z0 * $sigma + $mu);
}

function randomNormal($entered_val, $variance, $prepend_str) {
    // A wrapper around generateGaussian, which just makes sure everything is numeric
    // First we remove any "$" just in case
    $clean_val = str_replace("$", "", $entered_val);
    $entered_num = (float) $clean_val;
    $generated_num = generateGaussian($entered_num, $variance);
    // Now round to 2 decimal places
    $generated_str = $prepend_str . number_format($generated_num, 2, '.', '');
    return $generated_str;
}

function randomDiscreteUnif($entered_val, $radius, $prepend_str) {
    $clean_val = str_replace("$", "", $entered_val);
    $entered_num = (float) $clean_val;
    $lower_bound = $entered_num - $radius;
    $upper_bound = $entered_num + $radius;
    $discrete_rand = mt_rand($lower_bound, $upper_bound);
    // Round to 2 decimal places
    $generated_str = $prepend_str . number_format($discrete_rand, 2, '.', '');
    return $generated_str;
}

function randomFloat($min, $max) {
    return $min + lcg_value() * abs($max - $min);
}

function randomContinuousUnif($entered_val, $radius, $prepend_str) {
    $clean_val = str_replace("$", "", $entered_val);
    $entered_num = (float) $clean_val;
    $lower_bound = $entered_num - $radius;
    $upper_bound = $entered_num + $radius;
    $continuous_rand = randomFloat($lower_bound, $upper_bound);
    $generated_str = $prepend_str . number_format($continuous_rand, 2, '.', '');
    return $generated_str;
}

// Created as a "drop-in" so we can easily switch between randomFromAll
// and randomFromRemaining, hence the unused 2nd arg
function randomFromAll($opt_list, $chosen_opt){
    $rand_opt_index = array_rand($opt_list);
    $rand_opt = $opt_list[$rand_opt_index];
    return($rand_opt);
}

function randomCategorical($value_list) {
    return randomFromAll($value_list, null);
}

function getEnteredValue($var_name) {
    // First a string where we prepend "entered_", for getting the user-entered value
    $entered_var_name = "entered_" . $var_name;
    // Now see if it's in $_POST
    if (isset($_POST[$entered_var_name])) {
        return $_POST[$entered_var_name];
    } else {
        // Return the default value
        return $GLOBALS["defaults"][$var_name];
    }
}

// Now we loop through the characteristics array, generating values to return
foreach ($GLOBALS["characteristics"] as $ch_name => $ch_values) {
    //echo "<br>Characteristic name:";
    //echo $ch_name;
    // Need a string where we prepend "generated_", for returning
    $gen_var_name = "generated_" . $ch_name;
    //echo "<br>";
    //echo $gen_var_name;
    //echo "<br>Characteristic values:";
    //var_dump($ch_values);
    // Now we check if it's a special case -- NORM or UNIF -- or just a regular categorical var
    if ($ch_values[0] == "NORM") {
        $variance = $ch_values[1];
        $prepend_str = "";
        if (count($ch_values) > 2) {
            // Record the prepend string
            $prepend_str = $ch_values[2];
        }
        // And we also need to have the value the user entered
        $entered_val = getEnteredValue($ch_name);
        $GLOBALS["r"][$gen_var_name] = randomNormal($entered_val, $variance, $prepend_str);
    } else if ($ch_values[0] == "DISCRETE_UNIF") {
        $unif_radius = $ch_values[1];
        $prepend_str = "";
        if (count($ch_values) > 2) {
            // Record the prepend string
            $prepend_str = $ch_values[2];
        }
        $entered_val = getEnteredValue($ch_name);
        $GLOBALS["r"][$gen_var_name] = randomDiscreteUnif($entered_val, $unif_radius, $prepend_str);
    } else if ($ch_values[0] == "CONTINUOUS_UNIF") {
        $unif_radius = $ch_values[1];
        $prepend_str = "";
        if (count($ch_values) > 2) {
            // Record the prepend string
            $prepend_str = $ch_values[2];
        }
        $entered_val = getEnteredValue($ch_name);
        $GLOBALS["r"][$gen_var_name] = randomContinuousUnif($entered_val, $unif_radius, $prepend_str);
    } else {
        // (Note that we don't need to check the entered value here, since we just randomly pick
        // an option for the characteristic regardless of what the user entered)
        $GLOBALS["r"][$gen_var_name] = randomCategorical($ch_values);
    }
    //echo "<br>";
    //echo $GLOBALS["r"][$gen_var_name];
    //echo "<br>";
}

// Finally, before returning, we choose a random order for the characteristics
$shuffled_chars = $char_list;
shuffle($shuffled_chars);
for ($i = 0; $i < $num_chars; $i++) {
    // php arrays start at 0, but row numbers start at 1
    $row_num = $i + 1;
    $rn_padded = sprintf('%02d', $row_num);
    // The current row's characteristic name
    $name_var = "name_o1_r" . $rn_padded;
    $name_value = $shuffled_chars[$i];
    $GLOBALS["r"][$name_var] = $name_value;
    // The user-entered value for the current row's characteristic
    $cur_var = "cur_o1_r" . $rn_padded;
    $cur_value = getEnteredValue($name_value);
    $GLOBALS["r"][$cur_var] = $cur_value;
    // The server-generated value for the current row's characteristic
    $gen_var_name = "generated_" . $name_value;
    $val_var = "val_o1_r" . $rn_padded;
    $val_value = $GLOBALS["r"][$gen_var_name];
    $GLOBALS["r"][$val_var] = $val_value;
    // Finally, the display name for the current row's characteristic
    $disp_var = "disp_o1_r" . $rn_padded;
    $disp_val = $GLOBALS["table_display"][$name_value];
    $GLOBALS["r"][$disp_var] = $disp_val;
}

// For debugging
$debug = false;
if (isset($_POST["debug"])) {
    $debug = $_POST["debug"];
}
if ($debug) {
    $GLOBALS["r"]["debug"] = var_export($GLOBALS["r"], true);
}

// And now we encode everything as JSON and echo it back to Qualtrics
// Generate JSON-format response
$json_response = json_encode($GLOBALS["r"]);
// Save the response with a timestamp before returning (TODO, if needed)
echo $json_response;

function flip_yesno($current_var) {
    // Helper function to easily flip Yes/No responses
    if ($current_var == "Yes"){
        return("No");
    } else {
        return("Yes");
    }
}

function randomFromRemaining($opt_list, $chosen_opt){
    // Just takes a list with *all* options and removes the already-chosen one, then
    // randomly selects from among the remaining options
    // Since the RAND study draws two values *without replacement*, here we pretend that $suprespect is the
    // already-chosen first value, so draw the second value uniformly from (sup_respect_opts)\(suprespect)
    // (\ = set difference)
    $chosen_array = [$chosen_opt];
    $remaining_opts = array_diff($opt_list, $chosen_array);
    $rand_opt_index = array_rand($remaining_opts);
    $rand_opt = $remaining_opts[$rand_opt_index];
    return($rand_opt);
}

?>
