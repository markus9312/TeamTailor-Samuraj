jQuery(document).ready(function($) {
    // Handle Test API button click
    $('#test_api').on('click', function() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'coswift_test_api',
                api_key: $('#api_key').val() // Assuming you have an input field with ID 'api_key'
            },
            success: function(response) {
                var formattedJson = formatResponse(response);
                $('#coswift-json-response').text(formattedJson); // Make sure this ID matches your display div
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown);
            }
        });
    });

    // Handle Sync from TeamTailor button click with confirmation
    $('#sync_teamtailor').on('click', function() {
        if (confirm("You are about to Sync Jobs from TeamTailor, possibly replacing old entries, are you sure you want to continue?")) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'coswift_sync_teamtailor',
                    api_key: $('#api_key').val() // Pass the API key if needed
                },
                success: function(response) {
                    var formattedJson = formatResponse(response);
                    $('#coswift-json-response').text(formattedJson); // Update this ID as well
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('AJAX error:', textStatus, errorThrown);
                }
            });
        }
    });

    // Function to format the JSON response
    function formatResponse(response) {
        try {
            // If response is a string, parse it
            if (typeof response === "string") {
                response = JSON.parse(response);
            }
            // Format the parsed data as JSON with indentation
            return JSON.stringify(response, null, 4);
        } catch (e) {
            console.error('Error parsing JSON:', e);
            return 'Error parsing JSON: ' + e.message; // Display error message
        }
    }
});
