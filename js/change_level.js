document.getElementById("id_maxcomlvl").addEventListener("input", function() {
    const input = this.value.trim();
    const dropdown = document.getElementById("id_level");
    dropdown.innerHTML = ""; // Clear existing options

    if (!isNaN(input) && input > 0 && input <= 999) {
        for (var i = 1; i <= input; i++) {
            dropdown.options.add(new Option("" + i, "" + i));
        }
    } else {
        dropdown.options.add(new Option("Please select a valid Max Competency Level", "default"));
    }
});