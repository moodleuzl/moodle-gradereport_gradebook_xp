document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('confirm_button').addEventListener('click', function() {
        const user_input = document.getElementById('id_maxcomlvl').value;
        const dropdown_menu = document.getElementById('id_level');
        dropdown_menu.innerHTML = ''; // Clear previous options
        for (let i = 1; i <= user_input; i++) {
            const option = document.createElement('option');
            option.value = i;
            option.text = i;
            dropdown_menu.appendChild(option);
        }
        // Show the dropdown and submit button
        document.getElementById('id_level').classList.remove('hidden');
    });
});