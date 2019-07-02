jQuery(document).ready(function() {
    // console.log('tekst');
    var inputDay = $('#input-find-bydate_day');
    var inputMonth = $('#input-find-bydate_month');
    var inputYear = $('#input-find-bydate_year');
    
    // inputDay.on('input',function(){
    $(".input-find-date").on('input',function(){
        //var tekst = inputDay.val();
        $.ajax({
                // url: "/muo/ajax",
                url: "/contribution/indexAjax",
                type: "GET",
                data: {
                    day: inputDay.val(),
                    month: inputMonth.val(),
                    year: inputYear.val()
                },
                success: function (msg) {
                    $('#contribution_list').html(msg);
                     //var users_list = $("#users_list");
                     //users_list.replaceWith(response.find('#users_list'));
                }
                ,error: function (err) {
                    $("#contribution_list").text(err.Message);
                }
        });
    });
   
});