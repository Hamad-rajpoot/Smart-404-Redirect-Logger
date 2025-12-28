jQuery(function ($) {

    // Confirm before deleting a redirect
    $('.sfrl-delete-btn').on('click', function (e) {
        const confirmDelete = confirm("Are you sure you want to delete this redirect?");
        if (!confirmDelete) {
            e.preventDefault();
        }
    });

    // Basic validation for redirect form
    $('#sfrl-redirect-form').on('submit', function (e) {

        let fromUrl = $.trim($('input[name="from_url"]').val());
        let toUrl   = $.trim($('input[name="to_url"]').val());

        if (fromUrl === '' || toUrl === '') {
            alert("Both From URL and To URL are required.");
            e.preventDefault();
            return false;
        }

        // Force leading slash in from_url
        if (!fromUrl.startsWith('/')) {
            fromUrl = '/' + fromUrl;
            $('input[name="from_url"]').val(fromUrl);
        }
    });

});
