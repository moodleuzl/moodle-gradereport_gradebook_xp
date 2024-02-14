document.getElementById("id_maxcomlvl").addEventListener("input", function(event) {
    const inputField = event.target;
    let input = inputField.value.trim();
    const dropdown = document.getElementById("id_level");
    dropdown.innerHTML = ""; // Clear existing options

    // Remove non-numeric characters
    input = input.replace(/\D/g, ''); // \D matches any non-digit character

    // Update the value of the input field
    inputField.value = input;

    if (input !== '' && input > 0 && input <= 999) {
        for (let i = 1; i <= input; i++) {
            dropdown.options.add(new Option("" + i, "" + i));
        }
    } else {
        dropdown.options.add(new Option("Please select a valid Max Competency Level", "default"));
    }
});