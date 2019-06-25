jQuery(document).ready(function() {
    console.log('tekst');
    var input_find_user = $('#input-find-user');
    
    input_find_user.on('input',function(){
        var tekst = input_find_user.val();
        $.ajax({
                // url: "/muo/ajax",
                url: "/member/user/indexAjax",
                type: "GET",
                data: {
                    str: tekst
                },
                success: function (msg) {
                    $('#users_list').html(msg);
                     //var users_list = $("#users_list");
                     //users_list.replaceWith(response.find('#users_list'));
                }
                ,error: function (err) {
                    $("#kontener").text(err.Message);
                }
        });
    });
   
});