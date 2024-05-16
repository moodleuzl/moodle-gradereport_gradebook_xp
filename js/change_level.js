function updateDropdownOptions() {
    const input = document.getElementById("id_maxcomlvl").value.trim();
    const dropdown = document.getElementById("id_level");
    dropdown.innerHTML = ""; // Clear existing options

    if (!isNaN(input) && input > 0 && input <= 999) {
        for (var i = 1; i <= input; i++) {
            dropdown.options.add(new Option("" + i, "" + i));
        }
    } else {
        dropdown.options.add(new Option("Please select a valid Max Competency Level", "default"));
    }
}

document.addEventListener("DOMContentLoaded", function() {
    // Call the function when the DOM content is fully loaded
    updateDropdownOptions();

    // Attach event listener for input change on the maxcomlvl input field
    document.getElementById("id_maxcomlvl").addEventListener("input", updateDropdownOptions);
});