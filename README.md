## PHP script for running conjoint experiments via Qualtrics

**Demo server: [https://jjacobs.info/handle_qualtrics.php](https://jjacobs.info/handle_qualtrics.php)**

**Demo survey implementation: [https://cumc.co1.qualtrics.com/jfe/form/SV_3WM8qu26hqannYW](https://cumc.co1.qualtrics.com/jfe/form/SV_3WM8qu26hqannYW)**

NOTE BEFORE ALL THIS: Qualtrics does an annoying thing with the data it collects, where if you change the wording of a question even a little bit after you've already published it, it treats all responses to the previously-worded version as in the "trash bin", so you have to do some work to go and re-include that data in the final dataset. Because of this, I highly recommend just duplicating the survey if you want to make a change, and calling the new one version 2, version 3, etc. (since the variables it generates when calling web services can be kind of dauntingly-named to begin with)

1. **Setting up the server** (The hard part) You'll need a PHP-enabled web server on which the .php script will live. For example, I just put it in the `public_html` subfolder of my home folder on the Textlab server, so that the URL to access it was https://textlab.econ.columbia.edu/~jjacobs/handle_qualtrics.php. If you don't have access to a server like this, there are ways to host .php scripts for free (like Heroku) that I'm happy to show yall.
2. **Specifying characteristics on the server side** Once the .php file is in a web-accessible folder, you'll need to modify the arrays in `qualtrics_config.php` so that they contain the characteristics (the array keys) and the specification for each characteristic (the array values). So if your whole survey consisted of *categorical* characteristics A, B, and C, with values like a1, a2, a3, b1, b2, b3, and c1, c2, c3, the array returned by `qualtrics_config.php` would have a key-value pair like:

    ```php
    return array(
        "A" = array(
            "spec" => ["a1", "a2", "a3"]
        ),
        "B" => array(
            "spec" => ["b1", "b2", "b3"]
        ),
        "C" => array(
            "spec" => ["c1", "c2", "c3"]
        )
    );
    ```

    For non-categorical variables, if you want to generate randomly-sampled continuous values around the user-entered value, I have it set up to allow generating normally-distributed or [discrete or continuous] uniformly-distributed values (if you need something fancier just let me know). To specify those, for example if we wanted characteristic D to be generated via a normal distribution with variance 5 around the entered value, characteristic E to be generated via a discrete uniform distribution in the range +/- 3 units around the entered value, and characteristic F to be generated via a continuous uniform distribution in the range +/- 1 unit around the entered value, you'd add these to your `qualtrics_config.php` as:

    ```php
    return array(
        "A" => array(
            "spec" => ["a1", "a2", "a3"]
        ),
        "B" => array(
            "spec" => ["b1", "b2", "b3"]
        ),
        "C" => array(
            "spec" => ["c1", "c2", "c3"]
        )
        "D" => array(
            "spec" => ["NORMAL", 1, "$"]
        ),
        "E" => array(
            "spec" => ["DISCRETE_UNIF", 3]
        ),
        "F" => array(
            "spec" => ["CONTINUOUS_UNIF", 1]
        )
    );
    ```
    Note also the third argument for the spec of characteristic "D": the "$" in the 3rd slot tells the server that you want the final value to be prepended with "$" (since we used this to generate hourly wage values).

    With this setup, then, the .php script would return `generated_D_01`, `generated_D_02`, and so on (see below for the naming schema), sampled from a normal distribution with mean `entered_D` and variance 1; `generated_E_01`, `generated_E_02`, and so on, sampled from a discrete uniform distribution across [`entered_E` - 3, `entered_E` - 2, ..., `entered_E` + 2, `entered_E` + 3]; and finally `generated_F_01`, `generated_F_02`, and so on, sampled from a continuous uniform distribution over (`entered_F` - 1, `entered_F` + 1).

    The `"spec"` key is the main thing you need to specify for each characteristic, but you can also set default values using the `"default"` key, or exclude a characteristic from the table (meaning, generate and return a random value, but don't include it as part of the table-row randomization) using the `"excludeFromTable"` key. For example, if we change the above `qualtrics_config.php` code to

    ```php
    return array(
        "A" => array(
            "spec" => ["a1", "a2", "a3"],
            "default" => "a2"
        ),
        "B" => array(
            "spec" => ["b1", "b2", "b3"],
            "excludeFromTable" => "true"
        ),
        "C" => array(
            "spec" => ["c1", "c2", "c3"]
        )
        "D" => array(
            "spec" => ["NORMAL", 1, "$"],
            "default" => 15.00
        ),
        "E" => array(
            "spec" => ["DISCRETE_UNIF", 3]
        ),
        "F" => array(
            "spec" => ["CONTINUOUS_UNIF", 1]
        )
    );
    ```
    this gives the server default values to use in case one or more of the user-entered values isn't received for some reason (for example, if the user leaves a field blank). Since `D` is generated via a normal distribution around the user's entered D value with variance 5, if the server doesn't receive an entered wage value it will instead just generate `D` via a normal distribution with mean 15.00 and variance 5.

    Once these arrays are set up, all that's left is stuff on the Qualtrics survey flow editor side.

3. **Naming the user-entered values in Qualtrics Flow Editor** The first step once you have the Qualtrics page open is to click "Flow" at the top of the survey editor. In this first Set Embedded Data block I'm just giving explicit names like `entered_jobtitle` to the responses given to the survey questions up to that point, so that I can use these names across the remainder of the survey flow. This is the final product, but while setting this up it should provide you with menus that you can use to scroll through and select the correct question (e.g., the job title question here has id QID25, but I found that by just scrolling through the list of questions until I found the one that mentioned job title).

    ![00_set_embedded.png](img/00_set_embedded.png)

4. **Setting up the Web Service call in Qualtrics Flow Editor**: Next you'll need to create a Web Service call block and (importantly) specify every variable from the survey that you want to send to the .php script. In this case, I'm mostly just sending the variables created in the previous step. *Important*: you'll need to make sure that the variables you send to the .php script are all of the form `entered_<characteristic name>`, where `<characteristic name>` is one of the keys in the array returned by `qualtrics_config.php`. In the first example from step 2, therefore, you'd need to send `entered_A`, `entered_B`, and `entered_C` to the `handle_qualtrics.php` script)

    ![01_send_to_web_service.png](img/01_send_to_web_service.png)

5. **Telling Qualtrics how to handle the generated values**: Next, you'll need to specify what Qualtrics should call the variables it receives *back* from the .php script as a response. This part requires a bit of an in-depth explanation: we were randomizing absolutely everything, including the order in which the characteristics showed up (randomized row order), so the way it's set up now is there are just a ton of variables called `name_o1_01`, `cur_01_01`, `val_01_01`; `name_01_02`, `cur_01_02`, `val_01_r02`, and so on.

    * `name_<N>_<M>` stands for the *internal* name of the characteristic that, for offer `N`, should go in row `M`. We use some JS code to replace this with a nice-looking label for the user (see `highlight_table.js`)
    * `cur_<N>_<M>` stands for the *current* value of that characteristic (the value entered by the respondent).
    * `val_<N>_<M>` stands for the *generated* value of that characteristic (the value created by the .php script).

    Since we had 6 job offers with 12 characteristics each, we used the "Test" button in the Web Service Block specification to generate 3x6x12=216 different variables that the .php script returned, that we then assigned to Qualtrics variables using this schema:

    ![02_receive_from_web_service.png](img/02_receive_from_web_service.png)

    Note that while the `<varname>_<N>_<M>` variables are pretty unwieldy and not very human-readable, the server also generates human-readable variables like `generated_wage_<N>`, `generated_commute_<N>`, and so on, so you should also save these into Qualtrics embedded data fields like so:

    ![02b_human_readable.png](img/02b_human_readable.png)

    This will make your life much easier when downloading and analyzing the final dataset (see step 7 below).

6. **Displaying the generated values in a conjoint table**: Once you've finished this mapping from variables returned by the .php script to variables in Qualtrics, you can use them on any subsequent page! I'm also including the HTML code for the tables we generated here, in `table_code.html`, so you can see what that looks like, since it's a bit of a weird fusion of HTML code and Qualtrics-specific variable reference code. The table as it's set up there will look weird in the preview -- it will look like there are headers on both the left side and above each row -- but that's because it's set up to be responsive, meaning that if someone is viewing the survey on mobile it will auto-adjust the table setup so that they don't have to scroll left to right on their phone. (If you don't want to worry about the responsiveness, though, you can just make a basic standard HTML table) Notice that the notation `${e://Field/<variable name>}` just tells Qualtrics "replace this with whatever is in the embedded data field called `<variable name>`".

    ![03_conjoint_table.png](img/03_conjoint_table.png)

    (By the way, I have code that highlights all rows where the offered job value differs from the current job value, if that's useful. I just didn't include it here because it's kind of hackish/makes the code look more convoluted. Let me know if you want me to add this back in)

7. **Downloading the survey data at the end**: You should probably do some trial runs to make sure it works as intended, but once the full survey is launched and you have all the responses you need, when you go to download the final dataset from the Qualtrics interface it will have all of the variables you created (e.g., in the screenshots here, the dataset would have all of the `user_entered_<varname>`, `name_<N>_<M>`, `cur_<N>_<M>`,  `val_<N>_<M>`, and `generated_<varname>_<N>` values). However, since the `name`, `cur`, and `val` variables were only generated to specify a randomized display order, you shouldn't need to include these in the analysis, so you can drop them and instead just use the simpler `generated_<varname>_<N>` values.
