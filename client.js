$(document).ready(function()
{
    
});

function query()
{
    var baseurl = "atwdAPI.php?";
    var action = $('input[name=actionGroup]:checked', '#input').val();

    //If there is an action selected (GET should be default) commence with URL building.
    if($("#action").val())
    {
        switch (action) 
        {
            case 'GET':
                var from = $('#from').val();
                var to = $('#to').val();
                var amount = $('#amount').val();
                var type = $('input[name=typeGroup]:checked', '#input').val();
                var url = baseurl + from + "&" + to + "&" + amount + "&" + type;
                break;

            case 'PUT':
                break;

            case 'POST':
                break;

            case 'DELETE':
                break;

            default:
                console.log("No action selected!");
                break;
        }
        //Need to change action depending on which action is selected
    }
    else
    {
        //Handle the error and send message to the user.
        
    }
    
    $.ajax(
        {
            url: url,
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