<?php

///// PHP internal stuff, no need to change this
// (Hooray for PHP totally working like a normal language :|)
date_default_timezone_set('America/New_York');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', true);
ini_set('error_log',"/home/bitnami/php_errors.log");
// Required for currency formatting
setlocale(LC_MONETARY, 'en_US.UTF-8');

$GLOBALS["characteristics"] = array(
    "jobtitle" => ["Cashier & Front End", "Sales Associate", "Cart Attendant & Janitorial",
    "Stocker, Backroom, & Receiving", "Fresh Food Associate", "Asset Protection",
    "Automotive", "Pharmacy", "Vision", "Department Manager", "Remodel Associate"],
    ""
);
$GLOBALS["jobtitle_opts"] = ;

// (You could set default values for each variable here. As it's set up
// right now all the default values are just NULL. But writing it like
// this ensures that all the variables get created even if the value isn't
// received from Qualtrics for whatever reason)
$user_jobtitle = NULL;
if(isset($_POST['entered_jobtitle'])) {
    $user_jobtitle = $_POST['entered_jobtitle'];
}
$user_wage = NULL;
if(isset($_POST['entered_wage'])) {
    $user_wage = $_POST["entered_wage"];
}
$user_hrs = NULL;
if(isset($_POST['entered_hrs'])) {
    $user_hrs = $_POST["entered_hrs"];
}
$user_controlhrs = NULL;
if(isset($_POST['entered_controlhrs'])) {
    $user_controlhrs = $_POST["entered_controlhrs"];
}
$user_friends = NULL;
if(isset($_POST['entered_friends'])) {
    $user_friends = $_POST["entered_friends"];
}
$user_commute = NULL;
if(isset($_POST['entered_commute'])) {
    $user_commute = $_POST["entered_commute"];
}
$user_physical = NULL;
if(isset($_POST['entered_physical'])) {
    $user_physical = $_POST["entered_physical"];
}
$user_coworkers = NULL;
if(isset($_POST['entered_coworkers'])) {
    $user_coworkers = $_POST["entered_coworkers"];
}
$user_suprespect = NULL;
if(isset($_POST['entered_suprespect'])) {
    $user_suprespect = $_POST["entered_suprespect"];
}
$user_supfair = NULL;
if(isset($_POST['entered_supfair'])) {
    $user_supfair = $_POST["entered_supfair"];
}
$user_express = NULL;
if(isset($_POST['entered_express'])) {
    $user_express = $_POST["entered_express"];
}
$user_timeoff = NULL;
if(isset($_POST['entered_timeoff'])) {
    $user_timeoff = $_POST["entered_timeoff"];
}
$user_newskills = NULL;
if(isset($_POST['entered_newskills'])) {
    $user_newskills = $_POST["entered_newskills"];
}
// User metadata
// Postal
$user_postal = NULL;
if(isset($_POST['user_postal'])){
    $user_postal = $_POST['user_postal'];
}
//error_log("Postal: " . $user_postal . "\n");
// City
$user_city = NULL;
if(isset($_POST['user_city'])){
    $user_city = $_POST['user_city'];
}
//error_log("City: " . $user_city . "\n");
// State
$user_state = NULL;
if(isset($_POST['user_state'])){
    $user_state = $_POST['user_state'];
}
//error_log("State: " . $user_state . "\n");
$user_country = NULL;
if(isset($_POST['user_country'])){
    $user_country = $_POST['user_country'];
}
//error_log("Country: " . $user_country . "\n");

///// Debugging zone :3
//$post_str = var_export($_POST, true);
//error_log($post_str);
//$log_str = "Query String: " . $_SERVER['QUERY_STRING'] . "\n";
//$log_str = $log_str . "Entered Hours: " . $user_hours . "\n";
// Save it to a file real quick
//$filename = '/home/bitnami/query.txt';
//file_put_contents($filename, $log_str);

$GLOBALS["num_offers"] = 3;
$GLOBALS["characteristics"] = ["JobTitle","Hours","ControlHours","Friends","CommuteTime",
                               "PhysicalDemands","CoworkerHelp","SupervisorFairness",
                               "SelfExpression","PaidTimeOff","TransferableSkills"];
$GLOBALS["diff_bg"] = "background-color: #FFFF00;";
// From the 10 characteristics we have (outside of wage and suprespect), chooses one
// uniformly at random
//$rand_char_index = array_rand($GLOBALS["characteristics"]);
//$GLOBALS['randomized_char'] = $GLOBALS["characteristics"][$rand_char_index];
// NOPE for now, just always randomizing commute time
//$GLOBALS["randomized_char"] = "CommuteTime";
// VERSION 2.0: Now *all* the vars are randomized
$GLOBALS["randomized_chars"] = $GLOBALS["characteristics"];

function flip_yesno($current_var) {
    // Helper function to easily flip Yes/No responses
    if ($current_var == "Yes"){
        return("No");
    } else {
        return("Yes");
    }
}

function generateSchedule($schedule){
    $random_sched_index = array_rand($sched_options);
    $random_sched = $sched_options[$random_sched_index];
    return($random_sched);
    //echo json_encode($random_sched);
}

// Created as a "drop-in" so we can easily switch between randomFromAll
// and randomFromRemaining, hence the unused 2nd arg
function randomFromAll($opt_list, $chosen_opt){
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

function generateUniform(){
    $unif = mt_rand() / mt_getrandmax();
    return($unif);
}

function generateGaussian($mu, $sigma){
	   $u1 = mt_rand() / mt_getrandmax();
	   $u2 = mt_rand() / mt_getrandmax();
	   $z0 = sqrt(-2.0 * log($u1)) * cos(2 * pi() * $u2);
	   //z1 = sqrt(-2.0 * log(u1)) * sin(two_pi * u2);
	   return($z0 * $sigma + $mu);
}


function processJobTitle($jobtitle){
    $offer1 = $jobtitle;
    $offer2 = $jobtitle;
    $offer3 = $jobtitle;
    if (in_array("JobTitle", $GLOBALS["randomized_chars"])){
        $offer1 = randomFromAll($GLOBALS["jobtitle_opts"], $jobtitle);
        $offer2 = randomFromAll($GLOBALS["jobtitle_opts"], $jobtitle);
        $offer3 = randomFromAll($GLOBALS["jobtitle_opts"], $jobtitle);
    }
    $offer1_diff = (strcmp($offer1, $jobtitle) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer2_diff = (strcmp($offer2, $jobtitle) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer3_diff = (strcmp($offer3, $jobtitle) !== 0 ? $GLOBALS["diff_bg"] : "");
    return(array("current_jobtitle" => $jobtitle,
                 "offer1_jobtitle" => $offer1, "offer1_jobtitle_diff" => $offer1_diff,
                 "offer2_jobtitle" => $offer2, "offer2_jobtitle_diff" => $offer2_diff,
                 "offer3_jobtitle" => $offer3, "offer3_jobtitle_diff" => $offer3_diff));
}

function generateWageOfferOld($wage_num) {
    // v3.0: draw from a normal dist of *wages* rather than wage multipliers
    $rand_wage = generateGaussian($wage_num, 0.1*$wage_num);
    // Lower bound of $7.25 (the federal min wage)
    $offer_wage = max(7.25, $rand_wage);
    return($offer_wage);
}

function randomWage($wage_num) {
    // v1.0: mu=1, sigma=0.01. v2.0: mu=1, sigma=0.1.
    $wage_mult = generateGaussian(1.0, 0.1);
    $wage_mult = max(0.50, $wage_mult);
    $wage_mult = min(1.50, $wage_mult);
    $rand_wage = $wage_mult * $wage_num;
    // Also make sure the offer wage is at least $7.25/hr
    $rand_wage = max(7.25, $rand_wage);
    return($rand_wage);
}

// The probability that the generated wage gets rounded to the nearest dollar
$GLOBALS['p_dollar_round'] = 0.15;
function genWageOffer($current_wage_num){
    $offer_wage_num = randomWage($current_wage_num);
    $offer_wage_str = "$" . number_format($offer_wage_num, 2);
    if (generateUniform() <= $GLOBALS['p_dollar_round']) {
        $offer_wage_str = number_format($offer_wage_num, 0) . ".00";
        $offer_wage_num = floatval(str_replace("$", "", $offer_wage_str));
    }
    return(array("wage_str" => $offer_wage_str, "wage_num" => $offer_wage_num));
}

function processWage($entered_wage_str){
    $entered_wage_clean = str_replace("$", "", $entered_wage_str);
    $current_wage_num = floatval($entered_wage_clean);
    $current_wage_str = number_format($current_wage_num, 2);
    // old
    //$offer_wage_num = generateGaussian($current_wage_num, 1);
    // new
    $offer1 = genWageOffer($current_wage_num);
    $offer1_diff = (strcmp($offer1["wage_str"], $current_wage_str) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer2 = genWageOffer($current_wage_num);
    $offer2_diff = (strcmp($offer2["wage_str"], $current_wage_str) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer3 = genWageOffer($current_wage_num);
    $offer3_diff = (strcmp($offer3["wage_str"], $current_wage_str) !== 0 ? $GLOBALS["diff_bg"] : "");
    $output_arr = array("current_wage_str" => $current_wage_str, "current_wage_num" => $current_wage_num,
                        "offer1_wage_str" => $offer1["wage_str"], "offer1_wage_num" => $offer1["wage_num"], "offer1_wage_diff" => $offer1_diff,
                        "offer2_wage_str" => $offer2["wage_str"], "offer2_wage_num" => $offer2["wage_num"], "offer2_wage_diff" => $offer2_diff,
                        "offer3_wage_str" => $offer3["wage_str"], "offer3_wage_num" => $offer3["wage_num"], "offer3_wage_diff" => $offer3_diff);
    return($output_arr);
    //echo json_encode($output_arr);
}

$GLOBALS["hrs_opts"] = array("20 hours or less" => 20, "20-40 hours" => 30, "40 hours or more" => 40);
function genOfferHrs($cur_hrs){
    $hrs_opts_keys = array_keys($GLOBALS["hrs_opts"]);
    $offer_hrs_str = randomFromAll($hrs_opts_keys, $cur_hrs);
    $offer_hrs_num = $GLOBALS["hrs_opts"][$offer_hrs_str];
    return(array("hrs_str" => $offer_hrs_str, "hrs_num" => $offer_hrs_num));
}
    
function processHrs($hrs){
    error_log("In processHrs(). hrs=" . $hrs);
    $hrs_num = $GLOBALS["hrs_opts"][$hrs];
    $offer1 = array("hrs_str" => $hrs, "hrs_num" => $hrs_num);
    $offer2 = array("hrs_str" => $hrs, "hrs_num" => $hrs_num);
    $offer3 = array("hrs_str" => $hrs, "hrs_num" => $hrs_num);
    // Check if the offer hours needs to be randomized
    if (in_array("Hours", $GLOBALS["randomized_chars"])){
        $offer1 = genOfferHrs($hrs);
        $offer2 = genOfferHrs($hrs);
        $offer3 = genOfferHrs($hrs);
    }
    $offer1_diff = (strcmp($offer1["hrs_str"], $hrs) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer2_diff = (strcmp($offer2["hrs_str"], $hrs) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer3_diff = (strcmp($offer3["hrs_str"], $hrs) !== 0 ? $GLOBALS["diff_bg"] : "");
    return(array("current_hrs_str" => $hrs, "current_hrs_num" => $hrs_num,
                 "offer1_hrs_str" => $offer1["hrs_str"], "offer1_hrs_num" => $offer1["hrs_num"], "offer1_hrs_diff" => $offer1_diff,
                 "offer2_hrs_str" => $offer2["hrs_str"], "offer2_hrs_num" => $offer2["hrs_num"], "offer2_hrs_diff" => $offer2_diff,
                 "offer3_hrs_str" => $offer3["hrs_str"], "offer3_hrs_num" => $offer3["hrs_num"], "offer3_hrs_diff" => $offer3_diff));
}

function computeSalary($wage_num, $hrs_num){
    $salary_num = $hrs_num * $wage_num * 52;
    $salary_str = "$" . number_format($salary_num, 2);
    return(array("salary_num" => $salary_num, "salary_str" => $salary_str));
}

function computeSalaries($wage_arr, $hrs_arr){
    // Helper function which calls computeSalary() for all 4 jobs
    $cur_salary = computeSalary($wage_arr["current_wage_num"], $hrs_arr["current_hrs_num"]);
    $offer1_salary = computeSalary($wage_arr["offer1_wage_num"], $hrs_arr["offer1_hrs_num"]);
    $offer1_diff = (strcmp($offer1_salary["salary_str"], $cur_salary["salary_str"]) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer2_salary = computeSalary($wage_arr["offer2_wage_num"], $hrs_arr["offer2_hrs_num"]);
    $offer2_diff = (strcmp($offer2_salary["salary_str"], $cur_salary["salary_str"]) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer3_salary = computeSalary($wage_arr["offer3_wage_num"], $hrs_arr["offer3_hrs_num"]);
    $offer3_diff = (strcmp($offer3_salary["salary_str"], $cur_salary["salary_str"]) !== 0 ? $GLOBALS["diff_bg"] : "");
    return(array("current_salary_num" => $cur_salary["salary_num"], "current_salary_str" => $cur_salary["salary_str"],
                 "offer1_salary_num" => $offer1_salary["salary_num"], "offer1_salary_str" => $offer1_salary["salary_str"],
                 "offer2_salary_num" => $offer2_salary["salary_num"], "offer2_salary_str" => $offer2_salary["salary_str"],
                 "offer3_salary_num" => $offer3_salary["salary_num"], "offer3_salary_str" => $offer3_salary["salary_str"]));
}

$GLOBALS["controlhrs_opts"] = ["Yes", "No"];
function processControlHrs($controlhrs){
    $offer1_controlhrs = $controlhrs;
    $offer2_controlhrs = $controlhrs;
    $offer3_controlhrs = $controlhrs;
    if (in_array("ControlHours", $GLOBALS["randomized_chars"])){
        //$offer_controlhrs = flip_yesno($controlhrs);
        $offer1_controlhrs = randomFromAll($GLOBALS["controlhrs_opts"], $controlhrs);
        $offer2_controlhrs = randomFromAll($GLOBALS["controlhrs_opts"], $controlhrs);
        $offer3_controlhrs = randomFromAll($GLOBALS["controlhrs_opts"], $controlhrs);
    }
    $offer1_diff = (strcmp($offer1_controlhrs, $controlhrs) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer2_diff = (strcmp($offer2_controlhrs, $controlhrs) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer3_diff = (strcmp($offer3_controlhrs, $controlhrs) !== 0 ? $GLOBALS["diff_bg"] : "");
    return(array("current_controlhrs" => $controlhrs,
                 "offer1_controlhrs" => $offer1_controlhrs, "offer1_controlhrs_diff" => $offer1_diff,
                 "offer2_controlhrs" => $offer2_controlhrs, "offer2_controlhrs_diff" => $offer2_diff,
                 "offer3_controlhrs" => $offer3_controlhrs, "offer3_controlhrs_diff" => $offer3_diff));
}

$GLOBALS["friends_opts"] = ["None", "Some", "Many", "All"];
function processFriends($friends){
    $offer1_friends = $friends;
    $offer2_friends = $friends;
    $offer3_friends = $friends;
    if (in_array("Friends", $GLOBALS["randomized_chars"])){
        $offer1_friends = randomFromAll($GLOBALS["friends_opts"], $friends);
        $offer2_friends = randomFromAll($GLOBALS["friends_opts"], $friends);
        $offer3_friends = randomFromAll($GLOBALS["friends_opts"], $friends);
    }
    $offer1_diff = (strcmp($offer1_friends, $friends) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer2_diff = (strcmp($offer2_friends, $friends) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer3_diff = (strcmp($offer3_friends, $friends) !== 0 ? $GLOBALS["diff_bg"] : "");
    return(array("current_friends" => $friends, 
                 "offer1_friends" => $offer1_friends, "offer1_friends_diff" => $offer1_diff,
                 "offer2_friends" => $offer2_friends, "offer2_friends_diff" => $offer2_diff,
                 "offer3_friends" => $offer3_friends, "offer3_friends_diff" => $offer3_diff));
}

$GLOBALS["commute_opts"] = ["0-15 minutes", "15-30 minutes", "30-60 minutes", "More than 60 minutes"];
function processCommute($commute){
    $offer1_commute = $commute;
    $offer2_commute = $commute;
    $offer3_commute = $commute;
    if (in_array("CommuteTime", $GLOBALS["randomized_chars"])){
        $offer1_commute = randomFromAll($GLOBALS["commute_opts"], $commute);
        $offer2_commute = randomFromAll($GLOBALS["commute_opts"], $commute);
        $offer3_commute = randomFromAll($GLOBALS["commute_opts"], $commute);
    }
    $offer1_diff = (strcmp($offer1_commute, $commute) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer2_diff = (strcmp($offer2_commute, $commute) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer3_diff = (strcmp($offer3_commute, $commute) !== 0 ? $GLOBALS["diff_bg"] : "");
    return(array("current_commute" => $commute,
                 "offer1_commute" => $offer1_commute, "offer1_commute_diff" => $offer1_diff,
                 "offer2_commute" => $offer2_commute, "offer2_commute_diff" => $offer2_diff,
                 "offer3_commute" => $offer3_commute, "offer3_commute_diff" => $offer3_diff));
}

$GLOBALS["physical_opts"] = ["Yes","No"];
function processPhysical($physical){
    $offer1_physical = $physical;
    $offer2_physical = $physical;
    $offer3_physical = $physical;
    if (in_array("PhysicalDemands", $GLOBALS["randomized_chars"])) {
        //$offer_physical = flip_yesno($physical);
        $offer1_physical = randomFromAll($GLOBALS["physical_opts"], $physical);
        $offer2_physical = randomFromAll($GLOBALS["physical_opts"], $physical);
        $offer3_physical = randomFromAll($GLOBALS["physical_opts"], $physical);
    }
    $offer1_diff = (strcmp($offer1_physical, $physical) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer2_diff = (strcmp($offer2_physical, $physical) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer3_diff = (strcmp($offer3_physical, $physical) !== 0 ? $GLOBALS["diff_bg"] : "");
    return(array("current_physical" => $physical,
                 "offer1_physical" => $offer1_physical, "offer1_physical_diff" => $offer1_diff,
                 "offer2_physical" => $offer2_physical, "offer2_physical_diff" => $offer2_diff,
                 "offer3_physical" => $offer3_physical, "offer3_physical_diff" => $offer3_diff));
}

$GLOBALS["coworkers_opts"] = ["Almost Always", "Often", "Sometimes", "Never"];
function processCoworkers($coworkers){
    $offer1_coworkers = $coworkers;
    $offer2_coworkers = $coworkers;
    $offer3_coworkers = $coworkers;
    if (in_array("CoworkerHelp", $GLOBALS["randomized_chars"])){
        $offer1_coworkers = randomFromAll($GLOBALS["coworkers_opts"], $coworkers);
        $offer2_coworkers = randomFromAll($GLOBALS["coworkers_opts"], $coworkers);
        $offer3_coworkers = randomFromAll($GLOBALS["coworkers_opts"], $coworkers);
    }
    $offer1_diff = (strcmp($offer1_coworkers, $coworkers) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer2_diff = (strcmp($offer2_coworkers, $coworkers) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer3_diff = (strcmp($offer3_coworkers, $coworkers) !== 0 ? $GLOBALS["diff_bg"] : "");
    return(array("current_coworkers" => $coworkers,
                 "offer1_coworkers" => $offer1_coworkers, "offer1_coworkers_diff" => $offer1_diff,
                 "offer2_coworkers" => $offer2_coworkers, "offer2_coworkers_diff" => $offer2_diff,
                 "offer3_coworkers" => $offer3_coworkers, "offer3_coworkers_diff" => $offer3_diff));
}

$GLOBALS["suprespect_opts"] = ["Almost Always", "Often", "Sometimes", "Never"];
function processSupervisorRespect($suprespect){
    // This one is always randomized
    $offer1_suprespect = randomFromAll($GLOBALS['suprespect_opts'], $suprespect);
    $offer2_suprespect = randomFromAll($GLOBALS['suprespect_opts'], $suprespect);
    $offer3_suprespect = randomFromAll($GLOBALS['suprespect_opts'], $suprespect);
    $offer1_diff = (strcmp($offer1_suprespect, $suprespect) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer2_diff = (strcmp($offer2_suprespect, $suprespect) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer3_diff = (strcmp($offer3_suprespect, $suprespect) !== 0 ? $GLOBALS["diff_bg"] : "");
    return(array("current_suprespect" => $suprespect,
                 "offer1_suprespect" => $offer1_suprespect, "offer1_suprespect_diff" => $offer1_diff,
                 "offer2_suprespect" => $offer2_suprespect, "offer2_suprespect_diff" => $offer2_diff,
                 "offer3_suprespect" => $offer3_suprespect, "offer3_suprespect_diff" => $offer3_diff));
}

$GLOBALS["supfair_opts"] = ["Almost Always", "Often", "Sometimes", "Never"];
function processSupervisorFair($supfair){
    $offer1_supfair = $supfair;
    $offer2_supfair = $supfair;
    $offer3_supfair = $supfair;
    if (in_array("SupervisorFairness", $GLOBALS["randomized_chars"])){
        $offer1_supfair = randomFromAll($GLOBALS["supfair_opts"], $supfair);
        $offer2_supfair = randomFromAll($GLOBALS["supfair_opts"], $supfair);
        $offer3_supfair = randomFromAll($GLOBALS["supfair_opts"], $supfair);
    }
    $offer1_diff = (strcmp($offer1_supfair, $supfair) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer2_diff = (strcmp($offer2_supfair, $supfair) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer3_diff = (strcmp($offer3_supfair, $supfair) !== 0 ? $GLOBALS["diff_bg"] : "");
    return(array("current_supfair" => $supfair,
                 "offer1_supfair" => $offer1_supfair, "offer1_supfair_diff" => $offer1_diff,
                 "offer2_supfair" => $offer2_supfair, "offer2_supfair_diff" => $offer2_diff,
                 "offer3_supfair" => $offer3_supfair, "offer3_supfair_diff" => $offer3_diff));
}

$GLOBALS["express_opts"] = ["Almost Always", "Often", "Sometimes", "Never"];
function processExpress($express) {
    $offer1_express = $express;
    $offer2_express = $express;
    $offer3_express = $express;
    if (in_array("SelfExpression", $GLOBALS["randomized_chars"])){
        $offer1_express = randomFromAll($GLOBALS["express_opts"], $express);
        $offer2_express = randomFromAll($GLOBALS["express_opts"], $express);
        $offer3_express = randomFromAll($GLOBALS["express_opts"], $express);
    }
    $offer1_diff = (strcmp($offer1_express, $express) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer2_diff = (strcmp($offer2_express, $express) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer3_diff = (strcmp($offer3_express, $express) !== 0 ? $GLOBALS["diff_bg"] : "");
    return(array("current_express" => $express,
                 "offer1_express" => $offer1_express, "offer1_express_diff" => $offer1_diff,
                 "offer2_express" => $offer2_express, "offer2_express_diff" => $offer2_diff,
                 "offer3_express" => $offer3_express, "offer3_express_diff" => $offer3_diff));
}

$GLOBALS["timeoff_opts"] = ["0 days", "1-10 days", "11-20 days", "21 or more days"];
function processTimeOff($timeoff) {
    $offer1_timeoff = $timeoff;
    $offer2_timeoff = $timeoff;
    $offer3_timeoff = $timeoff;
    if (in_array("PaidTimeOff", $GLOBALS["randomized_chars"])){
        $offer1_timeoff = randomFromAll($GLOBALS["timeoff_opts"], $timeoff);
        $offer2_timeoff = randomFromAll($GLOBALS["timeoff_opts"], $timeoff);
        $offer3_timeoff = randomFromAll($GLOBALS["timeoff_opts"], $timeoff);
    }
    $offer1_diff = (strcmp($offer1_timeoff, $timeoff) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer2_diff = (strcmp($offer2_timeoff, $timeoff) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer3_diff = (strcmp($offer3_timeoff, $timeoff) !== 0 ? $GLOBALS["diff_bg"] : "");
    return(array("current_timeoff" => $timeoff,
                 "offer1_timeoff" => $offer1_timeoff, "offer1_timeoff_diff" => $offer1_diff,
                 "offer2_timeoff" => $offer2_timeoff, "offer2_timeoff_diff" => $offer2_diff,
                 "offer3_timeoff" => $offer3_timeoff, "offer3_timeoff_diff" => $offer3_diff));
}

$GLOBALS["newskills_opts"] = ["Yes", "No"];
function processNewSkills($newskills) {
    $offer1_newskills = $newskills;
    $offer2_newskills = $newskills;
    $offer3_newskills = $newskills;
    if (in_array("TransferableSkills", $GLOBALS["randomized_chars"])){
        //$offer_newskills = flip_yesno($newskills);
        $offer1_newskills = randomFromAll($GLOBALS["newskills_opts"], $newskills);
        $offer2_newskills = randomFromAll($GLOBALS["newskills_opts"], $newskills);
        $offer3_newskills = randomFromAll($GLOBALS["newskills_opts"], $newskills);
    }
    $offer1_diff = (strcmp($offer1_newskills, $newskills) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer2_diff = (strcmp($offer2_newskills, $newskills) !== 0 ? $GLOBALS["diff_bg"] : "");
    $offer3_diff = (strcmp($offer3_newskills, $newskills) !== 0 ? $GLOBALS["diff_bg"] : "");
    return(array("current_newskills" => $newskills,
                 "offer1_newskills" => $offer1_newskills, "offer1_newskills_diff" => $offer1_diff,
                 "offer2_newskills" => $offer2_newskills, "offer2_newskills_diff" => $offer2_diff,
                 "offer3_newskills" => $offer3_newskills, "offer3_newskills_diff" => $offer3_diff));
}

function genJobPostingLink($jobtitle, $postal, $salary_num){
    $urlroot = "https://www.careerbuilder.com/jobs?keywords=";
    // Job Title part
    $job = str_replace(" ", "+", strtolower($jobtitle));
    // Pay Level part
    $paylevel = round($salary_num, -3, PHP_ROUND_HALF_DOWN) / 1000;
    $paylevel_str = number_format($paylevel, 0);
    $full_url = $urlroot . $job . "&location=" . $postal . "&pay=" . $paylevel_str;
    //error_log("Full URL: " . $full_url . "\n");
    // And now the dollar amount for the text on top
    $displayed_salary = '$' . $paylevel_str . ',000+';
    return(array("url" => $full_url, "salary_str" => $displayed_salary));
}

function genJobPostingLinks($cur_jobtitle, $o1_jobtitle, $o2_jobtitle, $o3_jobtitle, $postal, $cur_salary_num, $o1_salary_num, $o2_salary_num, $o3_salary_num){
    // Just a helper function which calls genJobPostingLink 3 times and constructs the aggregate array
    $cur_job = genJobPostingLink($cur_jobtitle, $postal, $cur_salary_num);
    $offer1 = genJobPostingLink($o1_jobtitle, $postal, $o1_salary_num);
    $offer2 = genJobPostingLink($o2_jobtitle, $postal, $o2_salary_num);
    $offer3 = genJobPostingLink($o3_jobtitle, $postal, $o3_salary_num);
    return(array("current_url" => $cur_job["url"], "current_salary_str" => $cur_job["salary_str"],
                 "offer1_url" => $offer1["url"], "offer1_salary_str" => $offer1["salary_str"],
                 "offer2_url" => $offer2["url"], "offer2_salary_str" => $offer2["salary_str"],
                 "offer3_url" => $offer3["url"], "offer3_salary_str" => $offer3["salary_str"]));
}

// Now the actual generation
$response = array();

// Return which characteristic got randomized (outside of the two that are always randomized)
$response["randomized_chars"] = $GLOBALS["randomized_chars"];
// Job Title
$jobtitle_arr = processJobTitle($user_jobtitle);
// We'll need these further down
$jobtitle1 = $jobtitle_arr["offer1_jobtitle"];
$jobtitle2 = $jobtitle_arr["offer2_jobtitle"];
$jobtitle3 = $jobtitle_arr["offer3_jobtitle"];
$response = array_merge($response, $jobtitle_arr);
// Wage
// Generate random wage and formatted version of entered wage
$wage_arr = processWage($user_wage);
$response = array_merge($response, $wage_arr);
// Hours
// Generate table text for entered num hours
$hrs_arr = processHrs($user_hrs);
$response = array_merge($response, $hrs_arr);
// Now that we have this, use it to compute their yearly salary
$salary_arr = computeSalaries($wage_arr, $hrs_arr);
$salarycur = $salary_arr["current_salary_num"];
$salary1 = $salary_arr["offer1_salary_num"];
$salary2 = $salary_arr["offer2_salary_num"];
$salary3 = $salary_arr["offer3_salary_num"];
$response = array_merge($response, $salary_arr);
// Control over hours
$controlhrs_arr = processControlHrs($user_controlhrs);
$response = array_merge($response, $controlhrs_arr);
// Friends
$friends_arr = processFriends($user_friends);
$response = array_merge($response, $friends_arr);
// Commute
$commute_arr = processCommute($user_commute);
$response = array_merge($response, $commute_arr);
// Physical
$physical_arr = processPhysical($user_physical);
$response = array_merge($response, $physical_arr);
// Coworkers
$coworkers_arr = processCoworkers($user_coworkers);
$response = array_merge($response, $coworkers_arr);
// Suprespect
$suprespect_arr = processSupervisorRespect($user_suprespect);
$response = array_merge($response, $suprespect_arr);
// Supfair
$supfair_arr = processSupervisorFair($user_supfair);
$response = array_merge($response, $supfair_arr);
// Express
$express_arr = processExpress($user_express);
$response = array_merge($response, $express_arr);
// Time Off
$timeoff_arr = processTimeOff($user_timeoff);
$response = array_merge($response, $timeoff_arr);
// New Skills
$newskills_arr = processNewSkills($user_newskills);
$response = array_merge($response, $newskills_arr);
// CareerBuilder link
$link_arr = genJobPostingLinks($user_jobtitle, $jobtitle1, $jobtitle2, $jobtitle3, $user_postal, $salarycur, $salary1, $salary2, $salary3);
$response = array_merge($response, $link_arr);
// Generate JSON-format response
$json_response = json_encode($response);
// Save the response with a timestamp before returning (TODO, if needed)
echo $json_response;

?>
