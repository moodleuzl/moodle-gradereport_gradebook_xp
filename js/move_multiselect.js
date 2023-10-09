document.addEventListener("DOMContentLoaded", function () {
    // Get references to DOM elements
    const move_to_multiselect1_btn = document.querySelector('button[name="move_to_multiselect1"]');
    const move_to_multiselect2_btn = document.querySelector('button[name="move_to_multiselect2"]');
    const multiselect1 = document.querySelector('select[name="multiselect1[]"]');
    const multiselect2 = document.querySelector('select[name="multiselect2[]"]');
    const weightInput = document.querySelector('input[name="weight"]');
    const connectionsInput = document.querySelector('input[name="new_connections"]');

    function updateConnectionsData() {
        const connectionsData = Array.from(multiselect2.options).map(option => {
            const data = JSON.parse(option.value);
            const [name] = option.text.split(' | ');  // Get only the name part
            return {
                id: data.id,
                name: name,
                weight: data.weight || weightInput.value  // Use stored weight if available
            };
        });
        connectionsInput.value = JSON.stringify(connectionsData);
        console.log(connectionsData);
    }

    // Add click event listener to the ">>" button
    move_to_multiselect2_btn.addEventListener('click', function (event) {
        // Prevent default button action
        event.preventDefault();

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
