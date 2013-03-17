var AjaxCart = Class.create();
AjaxCart.prototype = {
  initialize: function(selector){
    this.buttonItems = $$(selector);
    this.refreshCartLink = '/checkout/ajax/cartblock';
    this.addRequest = false;
    this.refereshCartBlockRequest = false;
    this.onSuccessRequest = this._onSuccessRequest.bindAsEventListener(this);
    this.onCompleteRequest = this._onCompleteRequest.bindAsEventListener(this);
    this.buttonItems.invoke('observe','click',this._onButtonClick.bindAsEventListener(this));
  },
  
  _onButtonClick: function(event) {
    event.stop();
    var element = event.element().up('.button');
    if (this.addRequest == false) {      
      var link = element.href + 'ajax/1';
      this.addRequest = new Ajax.Request(link, {
        method: 'get',
        onCreate: this.showLoader(element),
        onComplete: this.onCompleteRequest,
        onFailure: function(response) {
          this.onCompleteRequest(element);
        },
        onSuccess: this.onSuccessRequest
      });
    }
  },
  
  showLoader: function(element) {
    element.setStyle({'position': 'relative'});    
    element.insert({
      bottom: new Element('p',{'class':'drop-loader'})
    });
  },
  
  _onCompleteRequest: function(element) {
    this.addRequest = false;
    $$('.button .drop-loader').invoke('remove');
  },
  
  _onSuccessRequest: function(transport) {
        
    if (transport && transport.responseJson) {
      response = transport.responseJson;
    }
    else if (transport && transport.responseText){
      try{
        response = eval('(' + transport.responseText + ')');
      }
      catch (e) {
        response = {};
      }
    }
    
    if (response.success == true) {
      this.refreshCartBlock();
    }
  },
  
  refreshCartBlock: function() {
    if (this.refereshCartBlockRequest != false) {
      this.refereshCartBlockRequest.transport.abort();
    }
    element = $$('.block-cart')[0];
    this.refereshCartBlockRequest = new Ajax.Request(this.refreshCartLink,{
      method: 'get',
      onCreate: this.showLoader(element),
      //onComplete: this.onCompleteRequest(element),
      onFailure: function(response) {
        this.onCompleteRequest(element);
      },
      onSuccess: function(response) {
                    html = response.responseText;
                    $$('.block-cart')[0].replace(html);
                  }
    });
  },
}

document.observe("dom:loaded", function() {
  ajaxCart = new AjaxCart('.btn-cart:not(.btn-cart-sold-out)');
});