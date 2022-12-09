Qualtrics.SurveyEngine.addOnload(function () {
    /*Place your JavaScript here to run when the page loads*/
    // Loop over table rows, highlighting them if the values differ
    // Uncomment the following if you want a console message to make sure
    // the code is running once the question is loaded
    //console.log("Highlighting Table");
    var table = document.getElementById("offertable");
    var trows = table.getElementsByTagName("tr");
    //console.dir(trows);
    for (var i = 0; i < trows.length; i++) {
        var cur_row = trows[i];
        if (cur_row.id != "tableheader") {
            // Now see if its a mobile or non-mobile formatted row
            if (cur_row.classList[0] == "compare-row") {
                // This is the only type of row we care about -- the other
                // type (without the compare-row class) is just the potential
                // expanded header if they have a mobile device.
                // (For these rows, the first child is the
                // var name, and the next 2 are the actual values)
                var cur_col_text = cur_row.children[1].textContent;
                var off_col_text = cur_row.children[2].textContent;
                if (cur_col_text != off_col_text) {
                    // The values are different, so highlight the row
                    cur_row.style.backgroundColor = highlightColor;
                } // End highlight row
            } // End check if "compare-row" in classes
        } // End check if id is not "tableheader"
    } // End loop over table rows
});
