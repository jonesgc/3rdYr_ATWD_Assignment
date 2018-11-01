function query()
{
    var baseurl = "atwdAPI.php?";
    var action =  document.querySelector('input[name=actionGroup]:checked').value;
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
                var url = baseurl +"from=" + from + "&" + "to=" + to + "&" + "amount=" + amount + "&" + "type=" + type;
                break;

            case 'PUT':
				var obj = {"code":"", "fname":"", "rate":"", "countries":"", "type":""};
                //var code = document.getElementById('from').value;
                //var amount = document.getElementById('amount').value;
                obj["code"] = document.getElementById('putCurCode').value;
                obj["fname"] = document.getElementById('putFname').value;
                obj["rate"] = document.getElementById('putRate').value;
                obj["locs"] = document.getElementById('putCountries').value;
                obj["type"] = document.querySelector('input[name=typeGroup]:checked').value;
				var param = JSON.stringify(obj);
                var url = "atwdAPI.php";
                break;

            case 'POST':
                //Need to be careful when using JSON in post as it causes an extra step to be needed on server side.
                //Best to create an alternative.
                var obj = {"code":"", "rate":"", "type":""};
                obj["code"] = document.getElementById('postCurCode').value;
                obj["rate"] = document.getElementById('postRate').value;
                obj["type"] = document.querySelector('input[name=typeGroup]:checked').value;
                var param = JSON.stringify(obj);
                var url = "atwdAPI.php";
                break;

            case 'DELETE':
                var obj = {"code":"","type":""};
                obj["code"] = document.getElementById("deleteCode").value;
                obj["type"] = document.querySelector('input[name=typeGroup]:checked').value;
                url = "atwdAPI.php";
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
              console.log(this.responseText.toString());
              document.getElementById('responseTextArea').value = this.responseText;
          }
          else if (action === 'POST')
          {
              console.log(this.responseText.toString());
              document.getElementById('responseTextArea').value = this.responseText;
          }
      }
    };

    req.open(action, url , true);

	if(action === 'PUT')
	{
		req.send(param);
    }
    else if(action === 'POST')
    {
        req.send(param);
    }
    else if(action === 'DELETE')
    {
        console.log("Hello");
        var delReq = new XMLHttpRequest();
        delReq.open("POST", url, true);
        delReq.onreadystatechange = function ()
        {
            if(this.readyState == 4 && this.status == 200)
            {
                console.log(this.responseText);
                document.getElementById('responseTextArea').value = this.responseText;
            }
        }
        delReq.setRequestHeader("action", "DELETE");
        delReq.send(param);
    }
	else
	{
		req.send();
	}

};

function inputControl()
{
    //This function executes if the put attribute is clicked.
    //Desired visible div is always at the top of each block.
    var radio  = document.querySelector('input[name=actionGroup]:checked').value;

    switch (radio)
    {
        case 'GET':
            document.getElementById('getInput').style = 'float:left';
            document.getElementById('putInput').style = 'display:none';
            document.getElementById('postInput').style = 'display:none';
            document.getElementById('deleteInput').style= 'display:none';
            break;
        case 'PUT':
            document.getElementById('putInput').style = 'float:left';
            document.getElementById('getInput').style = 'display:none';
            document.getElementById('postInput').style = 'display:none';
            document.getElementById('deleteInput').style = 'display:none';
            break;
        case 'POST':
            document.getElementById('postInput').style = 'float:left';
            document.getElementById('getInput').style = 'display:none';
            document.getElementById('putInput').style = 'display:none';
            document.getElementById('deleteInput').style = 'display:none';
            break;
        case 'DELETE':
            document.getElementById('deleteInput').style = 'float:left';
            document.getElementById('getInput').style = 'display:none';
            document.getElementById('postInput').style = 'display:none';
            document.getElementById('putInput').style = 'display:none';
            break;
        default:
            break;
    }

};
