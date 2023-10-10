document.addEventListener("DOMContentLoaded", function () {
    // Get references to DOM elements
    const move_to_multiselect1_btn = document.querySelector('button[name="move_to_multiselect1"]');
    const move_to_multiselect2_btn = document.querySelector('button[name="move_to_multiselect2"]');
    const multiselect1 = document.querySelector('select[name="multiselect1[]"]');
    const multiselect2 = document.querySelector('select[name="multiselect2[]"]');
    const weightInput = document.querySelector('input[name="weight"]');
    const connectionsInput = document.querySelector('input[name="connections"]');

    function updateConnectionsData() {
        const connectionsData = Array.from(multiselect2.options).map(option => {
            const data = JSON.parse(option.value);
            const split_text = option.text.split(' | ');
            const name = split_text.slice(0, -1).join(' | ');  // Get only the name part
            const weight = option.text.split(' | ').pop();  // Get only the weight part
            return {
                id: data.id,
                name: name,
                weight: Number(weight)  // Use stored weight if available
            };
        });
        connectionsInput.value = JSON.stringify(connectionsData);
        console.log(connectionsData);
    }
    updateConnectionsData();

    // Add click event listener to the ">>" button
    move_to_multiselect2_btn.addEventListener('click', function (event) {
        // Prevent default button action
        event.preventDefault();

        if (weightInput.value === '' || isNaN(weightInput.value)) {
            return;
        }

        // Get selected options from multiselect1
        const selectedOptions = Array.from(multiselect1.selectedOptions);

        // Loop through selected options and move them to multiselect2
        selectedOptions.forEach(option => {
            const data = JSON.parse(option.value);  // Parse the JSON value
            option.remove();

            const newOption = new Option(`${data.name} | ${weightInput.value}`, option.value);
            multiselect2.add(newOption);
        });
        updateConnectionsData();
    });

    // Add click event listener to the "<<" button
    move_to_multiselect1_btn.addEventListener('click', function (event) {
        // Prevent default button action
        event.preventDefault();

        // Get selected options from multiselect2
        const selectedOptions = Array.from(multiselect2.selectedOptions);

        // Loop through selected options and move them back to multiselect1
        selectedOptions.forEach(option => {
            const data = JSON.parse(option.value);  // Parse the JSON value
            option.remove();

            const newOption = new Option(data.name, option.value);  // Only use name for display
            multiselect1.add(newOption);
        });
        updateConnectionsData();
    });
});
