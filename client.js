function query()
{
    var baseurl = "atwdAPI.php?";
    var action =  document.querySelector('input[name=actionGroup]:checked').value;
    console.log(action);
    //If there is an action selected (GET should be default) commence with URL building.
    if(action)
    {
        switch (action)
        {
            case 'GET':
                var from = document.getElementById('from').value;
                var to = document.getElementById('to').value;
                var amount = document.getElementById('amount').value
                var type = document.querySelector('input[name=typeGroup]:checked').value;
                var url = baseurl + from + "&" + to + "&" + amount + "&" + type;
                break;

            case 'PUT':
				var obj = {"code":"", "rate":""};
                var code = document.getElementById('from').value;
                var amount = document.getElementById('amount').value;
				obj["code"] = code;
				obj["rate"] = amount;
				var param = JSON.stringify(obj);
                var url = "atwdAPI.php";
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
    var req = new XMLHttpRequest();
    req.onreadystatechange = function()
    {
      if (this.readyState == 4 && this.status == 200)
      {
		  if(action === 'GET')
		  {
			  console.log(this.responseText.toString());
	          document.getElementById('responseTextArea').value = this.responseText;
		  }
		  else if (action === 'PUT')
		  {
			  console.log(this.responseText);
		  }

      }
    };
    req.open(action, url , true);
	if(action === 'PUT')
	{
		req.send(param);
	}
	else
	{
		req.send();
	}

};

function putInput()
{

};
