jQuery(document).ready(function() {
    // console.log('tekst');
    
    // inputDay.on('input',function(){
        $(".input-generate-months").on('input',function(){
            var $initialAccount = $('#member_user_initialAccount').val();
            if ($initialAccount == '') $initialAccount = 0;
            var inputDay = $('#member_user_beginDate_day');
            var inputMonth = $('#member_user_beginDate_month');
            var inputYear = $('#member_user_beginDate_year');
            var inputJob = $('#member_user_job');
        $.ajax({
                url: "table_months_ajax",
                type: "GET",
                data: {
                    day: inputDay.val(),
                    month: inputMonth.val(),
                    year: inputYear.val(),
                    initialAccount: $initialAccount,
                    job: inputJob.val()
                },
                success: function (msg) {
                    $('#month_reckoning_div').html(msg);
                }
                ,error: function (err) {
                    $("#month_reckoning_div").text(err.Message);
                }
        });
    });
   
});