<?php
// These make our lives easier, so we don't have to keep rewriting "Yes","No"
$yesno = ["Yes", "No"];
$fourq = ["Almost Always", "Often", "Sometimes", "Never"];

// Each question should have a key in this array, and the values specify how
// they are generated (given the user-entered values regarding their current job)
// [This array is returned here and gets loaded by handle_qualtrics.php]
return array(
    "jobtitle" => array(
        // "Identity" spec, where it just returns the same value that the user originally entered
        // (Note that it's a string in this case, whereas for the rest of the examples it's an array)
        "spec" => "RETURN",
        // If they don't enter their job title or we don't receive the data for whatever reason,
        // we use the placeholder value "Current Job" (just to be safe)
        "default" => "Current Job",
        // Since we just use the job titles in the *header* of the table, we don't
        // need to randomize its position, so we set this to true (it defaults to false)
        "excludeFromTable" => "true"
    ),
    "wage" => array(
        // Normally distributed, with mean = user-entered wage and
        // sd = 5
        "spec" => ["NORM", 5, "$"],
        // If the user doesn't enter a wage, or data gets corrupted somehow,
        // default to mean=15 (and sd still = 5)
        "default" => 15.00
    ),
    "commute" => array(
        // Choose uniformly from among the four possible choices
        "spec" => ["0-15 minutes", "16-30 minutes", "31-60 minutes", "More than 60 minutes"],
        "default" => "16-30 minutes"
    ),
    "hrsweek" => array(
        // Uniform distribution on the integers in the range
        // [current - 3, current + 3], where current = user's entered hrs/week
        "spec" => ["DISCRETE_UNIF", 3],
        "default" => 20
    ),
    "healthcare" => array(
        // Here we use our template for Yes/No questions
        // (Since we don't specify a particular default value, the server
        // will just use the first option -- in this case "Yes" -- as the default)
        "spec" => $yesno
    ),
    "lunch" => array(
        // Draw from *continuous* uniform dist, over the range
        // [current - 5, current + 5], where current = user's entered lunch hrs
        "spec" => ["CONTINUOUS_UNIF", 5],
        // If we don't have data on user's lunch hrs, default to this continuous
        // uniform dist with range [10 - 5, 10 + 5] = [5, 15]
        "default" => 10
    )
);

?>