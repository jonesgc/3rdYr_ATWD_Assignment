$(document).ready(function()
{
    
});

function query()
{
    var baseurl = "http://localhost/atwd/atwd_assignment/atwdAPI.php?";
    var action = 
    //Check if there is something in the text box for the request
    if($("#request").val())
    {
      var request = $("#request").val();
    }
    else
    {
        //Nothing in the text box. Error should go in here.
    }
    
    $.ajax(
        {
            url: request,

            error: function()
            {
                $("#request").append("Failed AJAX call.");
            },
            
            type = action
        });
};