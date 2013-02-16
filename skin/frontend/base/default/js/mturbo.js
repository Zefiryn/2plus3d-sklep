var mturboloader = {
		
	url: '',
    blocks: '',
    complete: false,
    cartLink: false,
    blocksContents: new Array(),
    
    addBlockRequest: function(blockIdentifier) {
    	if (mturboloader.blocks=='') {
    		mturboloader.blocks = 'identifier[]='  + blockIdentifier;
    	} else {
    		mturboloader.blocks = mturboloader.blocks + '&identifier[]=' + blockIdentifier;
    	}
    },
    
    getBlock: function (blockIdentifier) {
		return mturboloader.blocksContents[blockIdentifier].replace(/&amp;MTURBO!/g, "&");
    },
    
    loadBlocks: function(url, referer) {
    	url = url+'?'+this.blocks+'&referer='+referer;
    	new Ajax.Request(url, {
    		method: "get",
    		onSuccess: 
    			function(transport) {

      				if (window.DOMParser) {
      					parser=new DOMParser();
      					xmlDoc=parser.parseFromString(transport.responseText, "text/xml");
      				}
      				else // Internet Explorer
      				{
      					xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
      					xmlDoc.async="false";
      					xmlDoc.loadXML(transport.responseText);
      				}
  					
      				var i;
      				var blocks = xmlDoc.childNodes[0].childNodes;
      				for (i=0; i<blocks.length; i++) {
      				
      					var name = blocks[i].getAttribute('name');
      					if (name!='') {
      						 try {
      							
      						    // Gecko-based browsers, Safari, Opera.
      							mturboloader.blocksContents[name] = (new XMLSerializer()).serializeToString(blocks[i]);
      						  }
      						  catch (e) {
      						    try {
      						      // Internet Explorer
      						      mturboloader.blocksContents[name] = blocks[i].xml;
      						    }
      						    catch (e)
      						    {}
      						  }
      					}
      				}
     
      				mturboloader.complete = true;
      				setTimeout('updateCartLink()', 100);
      			}
    	});
    }
    
}
function updateCartLink() {
	var re		= new RegExp('checkout/cart/$');
	var links	= document.getElementsByTagName('a');
	for (var i=0; i<links.length; i++) {
		if (re.exec(links[i].href)) {
			if (mturboloader.blocksContents['cartlink'])
				links[i].innerHTML = mturboloader.blocksContents['cartlink'];
			else {
				setTimeout('updateCartLink()', 100);
		        return;
			}
		}
	}
}