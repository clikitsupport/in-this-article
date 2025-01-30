jQuery(document).ready(function($) {
    $('#itah_copy_btn').on('click', function() {
        var $shortcodeInput = $('#itah-shortcode');
        $shortcodeInput.select();
        document.execCommand('copy');
        // Show an alert or change the button text to confirm the copy
        alert('Shortcode copied: ' + $shortcodeInput.val());
    });
});