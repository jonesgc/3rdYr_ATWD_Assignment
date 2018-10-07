$(document).ready(function()
{
    
});

function query()
{
    var baseurl = "http://localhost/atwd/atwd_assignment/atwdAPI.php?";
    var action = $('input[name=actionGroup]:checked', '#input').val();
    //Check if there is something in the text box for the request
    if($("#action").val())
    {
      var from = $('#from').val();
      var to = $('#to').val();
      var amount = $('#amount').val();
    }
    else
    {
        //Handle the error and send message to the user.
        
    }
    
    $.ajax(
        {
            url: baseurl + from + to + amount,
            type : action,
            success: function(data)
            {
                console.log(data);
            },
            error: function()
            {
                $("#request").append("Failed AJAX call.");
            },
            
            
        });
};