jQuery(function ($) {

    $('#pisol-credit-report').DataTable({
        "paging": false,
        "info": false,
        "dom": 'Bfrtip',
        buttons: [{
            extend: 'csv',
            text: 'Download this page'
        }]
    });

    $('#pi-download-report').on('click', function (e) {
        e.preventDefault();
        var start_date = $('#start_date').val();
        var end_date = $('#end_date').val();
        var filter_by = $('#filter_by').val();
        var email_id = $('#email_id').val();
        var coupon = $('#coupon').val();
        var page = 1; // Start from page 1
        var file_name = '';
        
    
        var button = $(this);
        var old_content = button.text();
        button.text('Processing');
    
        function downloadInChunks(page, file_name) {
            var data = {
                'action': 'pi_download_report',
                'start_date': start_date,
                'end_date': end_date,
                'filter_by': filter_by,
                'email_id': email_id,
                'coupon': coupon,
                'pi_coupon_report_page': page,
                'file_name': file_name
            };
            // Update the page number in the request data
            data.pi_coupon_report_page = page;
            button.attr('disabled', true);
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        if (response.data.complete) {
                            // File is ready, trigger the download
                            var file_url = response.data.file_url;
                            var a = document.createElement('a');
                            a.href = file_url;
                            a.download = 'store_credit_report.csv';
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            button.text(old_content);
                            button.prop('disabled', false);
                        } else {
                            // Not complete, process the next batch
                            downloadInChunks(response.data.next_page, response.data.file_name);
                            button.text('Processing ' + page);
                        }
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function() {
                    alert('Something went wrong. Please try again later.');
                }
            }).always(function () {
                if (response.data.complete) {
                    button.text(old_content);
                    button.prop('disabled', false);
                }
            });
        }
    
        // Start downloading in chunks from page 1
        downloadInChunks(1, file_name);
    });
    

});