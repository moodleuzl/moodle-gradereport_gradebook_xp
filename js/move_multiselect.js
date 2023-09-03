document.addEventListener("DOMContentLoaded", function() {
    // Get references to DOM elements
    const move_to_multiselect1_btn = document.querySelector('button[name="move_to_multiselect1"]');
    const move_to_multiselect2_btn = document.querySelector('button[name="move_to_multiselect2"]');
    const multiselect1 = document.querySelector('select[name="multiselect1[]"]');
    const multiselect2 = document.querySelector('select[name="multiselect2[]"]');
    const weightInput = document.querySelector('input[name="weight"]');

    // Add click event listener to the ">>" button
    move_to_multiselect2_btn.addEventListener('click', function(event) {
        // Prevent default button action
        event.preventDefault();

        // Get selected options from multiselect1
        const selectedOptions = Array.from(multiselect1.selectedOptions);

        // Loop through selected options and move them to multiselect2
        selectedOptions.forEach(option => {
            // Remove option from multiselect1
            option.remove();

            // Clone option object to append to multiselect2
            const newOption = new Option(`${option.text} | ${weightInput.value}`, option.value);

            // Append new option to multiselect2
            multiselect2.add(newOption);
        });
    });

    // Add click event listener to the "<<" button
    move_to_multiselect1_btn.addEventListener('click', function(event) {
        // Prevent default button action
        event.preventDefault();

        // Get selected options from multiselect2
        const selectedOptions = Array.from(multiselect2.selectedOptions);

        // Loop through selected options and move them back to multiselect1
        selectedOptions.forEach(option => {
            // Remove option from multiselect2
            option.remove();

            // Remove the weight part of the string
            const strippedText = option.text.split(' | ')[0];

            // Clone option object to append back to multiselect1
            const newOption = new Option(strippedText, option.value);

            // Append new option to multiselect1
            multiselect1.add(newOption);
        });
    });
});
