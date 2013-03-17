var AjaxCart = Class.create();
AjaxCart.prototype = {
  initialize: function(selector, bindEvents){
    
    this.refreshCartLink = '/checkout/ajax/cartblock';
    this.addRequest = false;
    this.refereshCartBlockRequest = false;
    this.onSuccessRequest = this._onSuccessRequest.bindAsEventListener(this);
    this.onCompleteRequest = this._onCompleteRequest.bindAsEventListener(this);    
    if (bindEvents) {
      this.buttonItems = $$(selector);
      this.buttonItems.invoke('observe','click',this._onButtonClick.bindAsEventListener(this));
      this.overrideProductFormSubmition();
    }
  },
  
  _onButtonClick: function(event) {
    event.stop();
    var button = event.element().up('.button');
    var element = button.href ? button : button.up('form');
    
    if (this.addRequest == false) {
      var link = element.href ? element.href : element.action;
      link += 'ajax/1';
      this.addRequest = new Ajax.Request(link, {
        method: 'get',
        onCreate: this.showLoader(button),
        onComplete: this.onCompleteRequest,
        onFailure: function(response) {
          this.onCompleteRequest(button);
        },
        onSuccess: this.onSuccessRequest
      });
    }
  },
  
  overrideProductFormSubmition: function() {
    //hack as browsers do not fire submit event when using form.submit() method
    if ($('product_addtocart_form')) {
      $('product_addtocart_form').submit = function(){return false;}
    }
  },
  
  showLoader: function(element) {
    element.setStyle({'position': 'relative'});    
    element.insert({
      bottom: new Element('p',{'class':'drop-loader'})
    });
  },
  
  _onCompleteRequest: function() {
    this.addRequest = false;
    $$('.btn-cart').invoke('removeAttribute','disabled');
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
      this.showInfo(response.info, 'success');
      this.refreshCartBlock();
    }
    else {
      this.showInfo(response.info, 'error');
    }
  },
  
  createMessageBlock: function() {
    var wrapper = new Element('div',{'class' : 'ajax-message-wrapper'});
    wrapper.insert({bottom: new Element('ul', {'class':'messages-list'})});
    
    return wrapper;
  },
  
  showInfo: function(messages, type) {
    var wrapper = this.createMessageBlock();
    wrapper.addClassName(type);
    var list = wrapper.down('ul');
    messages.each(function(msg){
      list.insert({bottom: new Element('li', {'class':'msg-item'}).update(msg)});
    });
    var close = new Element('a', {'class':'btn-close', 'id' : 'ajax-cart-btn-close'}).update(Translator.translate('Close'));    
    list.insert({bottom: new Element('li', {'class':'msg-item'}).update(close)});
    document.body.insert({top: wrapper});
    this.positionInfo(wrapper);
    this.bindHideInfo();
  },
  
  positionInfo: function(element) {
    var width = document.viewport.getWidth();
    var height = document.viewport.getHeight();
    var left = (width - element.getWidth()) / 2;
    var top = (height- element.getHeight()) / 2;    
    element.setStyle({
      'left': left+'px',
      'top': top+'px'
    });
  },
  
  bindHideInfo: function() {
    $('ajax-cart-btn-close').observe('click', function() {
      $$('.ajax-message-wrapper').invoke('remove');
    });
  },
  
  refreshCartBlock: function() {
    if (this.refereshCartBlockRequest != false) {
      this.refereshCartBlockRequest.transport.abort();
    }
    element = $$('.block-cart')[0];
    this.refereshCartBlockRequest = new Ajax.Request(this.refreshCartLink,{
      method: 'get',
      parameters: {'backlink': encodeURIComponent(window.location.href)},
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
  }
}

document.observe("dom:loaded", function() {
  ajaxCart = new AjaxCart('.btn-cart:not(.btn-cart-sold-out)', true);
});