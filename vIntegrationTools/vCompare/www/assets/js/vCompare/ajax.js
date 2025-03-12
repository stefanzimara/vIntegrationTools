

var getNodeValue = function(parent, tagName)
{
  var node = parent.getElementsByTagName(tagName)[0];
  return (node && node.firstChild) ? node.firstChild.nodeValue : '';
}

function processAjaxResponse(req) {

	var parseXml;
	
	if (window.DOMParser) {
	    parseXml = function(xmlStr) {
	        return ( new window.DOMParser() ).parseFromString(xmlStr, "text/xml");
	    };
	} else if (typeof window.ActiveXObject != "undefined" && new window.ActiveXObject("Microsoft.XMLDOM")) {
	    parseXml = function(xmlStr) {
	        var xmlDoc = new window.ActiveXObject("Microsoft.XMLDOM");
	        xmlDoc.async = "false";
	        xmlDoc.loadXML(xmlStr);
	        return xmlDoc;
	    };
	} else {
	    parseXml = function() { return null; }
	}

	var xmlDoc = parseXml(req);
	var response  = xmlDoc.documentElement;
	
	var commands = response.getElementsByTagName('command');

    for(var i=0; i < commands.length; i++) {
      method = commands[i].getAttribute('method');

     
      switch(method) {

        case 'alert':
          var message = getNodeValue(commands[i], 'message');
          window.alert(message);
          break;

        case 'setvalue':
          var target = getNodeValue(commands[i], 'target');
          var value = getNodeValue(commands[i], 'value');

          if(target && value != null) {
        	  
        	  var elementExists = document.getElementById(target);  
        	  
        	  if (typeof(elementExists) != 'undefined' && elementExists != null) {
        		  document.getElementById(target).value = value;
        	  } else {
        		  console.log(target + " not found");
        	  }
          }
          break;

        case 'setinnertext':
            var target = getNodeValue(commands[i], 'target');
            var text = getNodeValue(commands[i], 'text');
            if(target && text != null) {
              document.getElementById(target).innerText = text;
            }
            break;

        case 'setIndex':
            var target = getNodeValue(commands[i], 'target');
            var index = getNodeValue(commands[i], 'index');
            if(target && text != null) {
              document.getElementById(target).selectedIndex = index;
            }
            break;

            
        case 'setSelect':
        	
            var target = getNodeValue(commands[i], 'target');
            var options = response.getElementsByTagName('option');

            var mySelect = "#" + target;
            $(mySelect).empty();
            
			console.debug("Number of Entries: " + options.length);

            for(var o = 0; o < options.length; o++) {            
            	
            	 $(mySelect).append($('<option>', { 
            	        value: options[o].getAttribute("value"),
            	        text : options[o].textContent 
            	    }));

					console.debug("Value:" + options[o].getAttribute("value") + " / Text: " + options[o].textContent );
            }
            
            break;

        case 'setdefault':
          var target = getNodeValue(commands[i], 'target');
          if(target) {
            document.getElementById(target).value = document.getElementById(target).defaultValue;
          }
          break;

        case 'focus':
          var target = getNodeValue(commands[i], 'target');
          if(target) {
            document.getElementById(target).focus();
          }
          break;

        case 'setcontent':
          var target = getNodeValue(commands[i], 'target');
          var content = getNodeValue(commands[i], 'content');
          
          if(target && content != null) {
            document.getElementById(target).innerHTML = content;
          }

          break;

        case 'setstyle':
          var target = getNodeValue(commands[i], 'target');
          var property = getNodeValue(commands[i], 'property');
          var value = getNodeValue(commands[i], 'value');
          if(target && property && value) {
            document.getElementById(target).style[property] = value;
          }
          break;

        case 'setproperty':
          var target = getNodeValue(commands[i], 'target');
          var property = getNodeValue(commands[i], 'property');
          var value = getNodeValue(commands[i], 'value');
          if(value == "true") value = true;
          if(value == "false") value = false;
          if(target) {
            document.getElementById(target)[property] = value;
          }
          break;

        case 'eval':
            var cmd = getNodeValue(commands[i], 'eval');
            eval(cmd);
            break;
          
          
        case 'info':
            var info = getNodeValue(commands[i], 'info');
            break;
          
          
        default:
          window.console.log("Error: unrecognised method '" + method + "' in processReqChange()");
      }
    }
  

	
}

