// Wait for the document to be ready
$(document).ready(function () {
    // Remove the 'active' class from the <a> element with data-key="grades"
    $('.active.active_tree_node').removeClass('active');
    $('li[data-key="gb_xp"] a.nav-link').addClass('active');
});