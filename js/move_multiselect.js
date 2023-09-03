function moveSelectedItems(fromSelect, toSelect) {
    // Loop through each option of the fromSelect select box
    Array.from(fromSelect.options).forEach(function(option) {
        if (option.selected) {
            // Add to the toSelect select box
            const newOption = new Option(option.text, option.value);
            toSelect.add(newOption);

            // Remove from the fromSelect select box
            fromSelect.remove(option.index);
        }
    });
}
document.addEventListener("DOMContentLoaded", function() {
    const moveToRightBtn = document.querySelector('#id_move_to_multiselect2');
    const moveToLeftBtn = document.querySelector('#id_move_to_multiselect1');
    const assignmentsSelect = document.querySelector('#id_multiselect1');
    const connectionsSelect = document.querySelector('#id_multiselect2');

    moveToRightBtn.addEventListener('click', function(e) {
        e.preventDefault();
        moveSelectedItems(assignmentsSelect, connectionsSelect);
    });

    moveToLeftBtn.addEventListener('click', function(e) {
        e.preventDefault();
        moveSelectedItems(connectionsSelect, assignmentsSelect);
    });
});
