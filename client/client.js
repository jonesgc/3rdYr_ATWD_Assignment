function query()
{
    var baseurl = "../index.php?";
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
                var url = baseurl +"from=" + from + "&" + "to=" + to + "&" + "amnt=" + amount + "&" + "format=" + type;
                break;

            case 'PUT':
				var obj = {"code":"", "rate":"", "type":""};
                //var code = document.getElementById('from').value;
                //var amount = document.getElementById('amount').value;
                obj["code"] = document.getElementById('putCurCode').value;
                var type = document.querySelector('input[name=typeGroup]:checked').value;
                obj["type"] = type;
                var param = JSON.stringify(obj);
                var url = "../index.php";
                break;

            case 'POST':
                //Need to be careful when using JSON in post as it causes an extra step to be needed on server side.
                //Best to create an alternative.
                var obj = {"code":"", "rate":"", "type":""};
                obj["code"] = document.getElementById('postCurCode').value;
                obj["rate"] = document.getElementById('postRate').value;
                var type = document.querySelector('input[name=typeGroup]:checked').value;
                obj["type"] = type;
                var param = JSON.stringify(obj);
                //This string is used to test the $_POST method in php. 
                var test = "code=" + document.getElementById('postCurCode').value + "&" + "rate=" + document.getElementById('postRate').value + "&" + "type=" + type;
                var url = "../index.php";
                break;

            case 'DELETE':
                var obj = {"code":"","type":""};
                obj["code"] = document.getElementById("deleteCode").value;
                var type = document.querySelector('input[name=typeGroup]:checked').value;
                obj["type"] = type;
                var param = JSON.stringify(obj);
                url = "../index.php";
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
        var content = req.getResponseHeader("Content-Type");
        console.log(content);

        if(content === "text/xml")
        {
            console.log(this.responseText.toString());
            document.getElementById('responseTextArea').value = this.responseText;
        }
        else if(content === "text/json")
        {   
            console.log(this.responseText);
            var jsonStr = this.responseText;
            var jsonPP = JSON.stringify(JSON.parse(jsonStr), null, 2);
            document.getElementById('responseTextArea').value = jsonPP;
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
        req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        req.send(test);
    }
    else if(action === 'DELETE')
    {
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
