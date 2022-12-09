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

function includeInTable($ch_data) {
    return(!array_key_exists("excludeFromTable", $ch_data));
}

// Load the specific conjoint info from qualtrics_config.php, in the same folder
$GLOBALS["config"] = include("qualtrics_config.php");
$GLOBALS["config_table"] = array_filter($GLOBALS["config"], "includeInTable");
$num_chars = count($GLOBALS["config"]);
$num_chars_table = count($GLOBALS["config_table"]);
$char_list = array_keys($GLOBALS["config"]);
$char_list_table = array_keys($GLOBALS["config_table"]);

// This will be iteratively updated to contain the array we want to return to Qualtrics
// ("r" for "return")
$GLOBALS["r"] = array();

///// Debugging zone :3
//$post_str = var_export($_POST, true);
//error_log($post_str);
//$log_str = "Query String: " . $_SERVER['QUERY_STRING'] . "\n";
//$log_str = $log_str . "Entered Hours: " . $user_hours . "\n";
// Save it to a file real quick
//$filename = '/home/bitnami/query.txt';
//file_put_contents($filename, $log_str);

$GLOBALS["r"]["num_offers"] = 30;
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
// VERSION 3.0: Randomization is determined by qualtrics_config.php

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

function randomCategorical($value_list) {
    // You can change this to use randomFromRemaining() if you want that instead
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
        // If default is defined, return that
        if (array_key_exists("default", $GLOBALS["config"][$var_name])) {
          return $GLOBALS["config"][$var_name]["default"];
        } else {
          // Otherwise, just return the first value in the spec
          return $GLOBALS["config"][$var_name]["spec"][0];
        }
    }
}

// Generate one set of job characteristics per offer
for ($i = 0; $i < $GLOBALS["r"]["num_offers"]; $i++) {
  // $i starts at 0, but $offer_num starts at one
  $offer_num = $i + 1;
  $on_padded = sprintf('%02d', $offer_num);
  // Now we loop through the characteristics array, generating values for the current offer
  foreach ($GLOBALS["config"] as $ch_name => $ch_data) {
      // STEP 1: Generate the random values
      //echo "<br>Characteristic name:";
      //echo $ch_name;
      // Need a string where we prepend "generated_", for returning
      $gen_var_name = "generated_" . $ch_name . "_" . $on_padded;
      //echo "<br>";
      //echo $gen_var_name;
      //echo "<br>Characteristic values:";
      //var_dump($ch_values);
      $ch_spec = $ch_data["spec"];
      // Now we check if it's a special case -- RETURN, NORM, or UNIF -- or just a regular categorical var
      if ($ch_spec == "RETURN" || $ch_spec == ["RETURN"]) {
          $entered_val = getEnteredValue($ch_name);
          $GLOBALS["r"][$gen_var_name] = $entered_val;
      } else if ($ch_spec[0] == "NORM") {
          $variance = $ch_spec[1];
          $prepend_str = "";
          if (count($ch_spec) > 2) {
              // Record the prepend string
              $prepend_str = $ch_spec[2];
          }
          // And we also need to have the value the user entered
          $entered_val = getEnteredValue($ch_name);
          $GLOBALS["r"][$gen_var_name] = randomNormal($entered_val, $variance, $prepend_str);
      } else if ($ch_spec[0] == "DISCRETE_UNIF") {
          $unif_radius = $ch_spec[1];
          $prepend_str = "";
          if (count($ch_spec) > 2) {
              // Record the prepend string
              $prepend_str = $ch_spec[2];
          }
          $entered_val = getEnteredValue($ch_name);
          $GLOBALS["r"][$gen_var_name] = randomDiscreteUnif($entered_val, $unif_radius, $prepend_str);
      } else if ($ch_spec[0] == "CONTINUOUS_UNIF") {
          $unif_radius = $ch_spec[1];
          $prepend_str = "";
          if (count($ch_spec) > 2) {
              // Record the prepend string
              $prepend_str = $ch_spec[2];
          }
          $entered_val = getEnteredValue($ch_name);
          $GLOBALS["r"][$gen_var_name] = randomContinuousUnif($entered_val, $unif_radius, $prepend_str);
      } else {
          // (Note that we don't need to check the entered value here, since we just randomly pick
          // an option for the characteristic regardless of what the user entered)
          $GLOBALS["r"][$gen_var_name] = randomCategorical($ch_spec);
      }
      //echo "<br>";
      //echo $GLOBALS["r"][$gen_var_name];
      //echo "<br>";
  }
  // STEP 2: Choose a random *order* for the generated characteristics
  $shuffled_chars = $char_list_table;
  shuffle($shuffled_chars);
  for ($j = 0; $j < $num_chars_table; $j++) {
      // php arrays start at 0, but row numbers start at 1
      $row_num = $j + 1;
      $rn_padded = sprintf('%02d', $row_num);
      // The current row's characteristic name
      $name_var = "name_" . $on_padded . "_" . $rn_padded;
      $name_value = $shuffled_chars[$j];
      $GLOBALS["r"][$name_var] = $name_value;
      // The user-entered value for the current row's characteristic
      $cur_var = "cur_" . $on_padded . "_" . $rn_padded;
      $cur_value = getEnteredValue($name_value);
      $GLOBALS["r"][$cur_var] = $cur_value;
      // The server-generated value for the current row's characteristic
      $gen_var_name = "generated_" . $name_value . "_" . $on_padded;
      $val_var = "val_" . $on_padded . "_" . $rn_padded;
      $val_value = $GLOBALS["r"][$gen_var_name];
      $GLOBALS["r"][$val_var] = $val_value;
  } // end loop over rows
} // end loop over offers

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

function randomFromAll($opt_list){
    // Just takes a list with *all* options and removes the already-chosen one, then
    // randomly selects from among the remaining options
    // Since the RAND study draws two values *without replacement*, here we pretend that $suprespect is the
    // already-chosen first value, so draw the second value uniformly from (sup_respect_opts)\(suprespect)
    // (\ = set difference)
    $rand_opt_index = array_rand($opt_list);
    $rand_opt = $opt_list[$rand_opt_index];
    return($rand_opt);
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