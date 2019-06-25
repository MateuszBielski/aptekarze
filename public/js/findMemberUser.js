jQuery(document).ready(function() {
    console.log('tekst');
    var input_find_user = $('#input-find-user');
    
    input_find_user.on('input',function(){
        var tekst = input_find_user.val();
        // console.log(tekst);
       
        // console.log(input_find_user.val());
        // var szczepionkaSelektor = $(this);
        //$('#kontener').text(szczepionkaSelektor.val());
        $.ajax({
                url: "/member/user/indexAjax",
                type: "GET",
                dataType: "JSON",
                data: {
                    str: tekst
                },
                success: function (msg) {
                    $("#kontener").html(msg);
                     //var users_list = $("#users_list");
                     //users_list.replaceWith(response.find('#users_list'));
                }
                // ,error: function (err) {
                //     $("#kontener").text('błąd');
                // }
        });
    });
   
});