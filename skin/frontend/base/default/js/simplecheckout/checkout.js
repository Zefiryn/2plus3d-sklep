if (typeof history.replaceState !== 'function') {
  //IE
  history.replaceState = function(){};
  history.pushState = function(obj, step, newlink) {};
}

Validation.add('required-entry', 'Please fill out this field.', function(v) {
                return !Validation.get('IsEmpty').test(v);}
              );
Validation.add('set-cc-type', 'We are unable to recoginze this card type. Please provide Visa, Mastercard, American Express or Discover card number.', function(v) {
    var valid = false;
    var cc_regex = {
      VI: /^4[0-9]{12}(?:[0-9]{3})?$/,
      MC: /^5[1-5][0-9]{14}$/,
      AE: /^3[47][0-9]{13}$/,
      DI: /^6(?:011|5[0-9]{2})[0-9]{12}$/
    };    
    for (var cctype in cc_regex) {
      regexp = cc_regex[cctype];
      $('authorizenet_cc_number').removeClassName('cc-type-' + cctype);
      if (regexp.test(v)) {
        $('authorizenet_cc_type').value = cctype;        
        $('authorizenet_cc_number').addClassName('cc-type-' + cctype);
        valid = true;
      }
    }
    return valid;
});

SimpleCheckoutLink = Class.create();
SimpleCheckoutLink.prototype = {
  initialize: function(checkoutUrl, loginUrl, loggedIn){
    this.checkoutUrl = checkoutUrl;
    this.loginUrl = loginUrl;
    this.customerLoggedIn = loggedIn;
    this.onSuccess = this.insertForm.bindAsEventListener(this);
    this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    this.onFailure = this.alertFailure.bindAsEventListener(this);
    this.onLogin = this.parseLoginResponse.bindAsEventListener(this);
    this.onRegister = this.parseRegisterResponse.bindAsEventListener(this);
    this.onRemember= this.parseRememberResponse.bindAsEventListener(this);
    this.loginEnabled = false;
    this.form = null;
  },
          
  gotoCheckout: function() {
    if (this.customerLoggedIn) {
      window.location = this.checkoutUrl;
    }
    else {
      if (this.loginEnabled === false) {
        this.showLoader($('simplecheckout-link-container'));
        var request = new Ajax.Request(this.loginUrl,
                        {
                          method: 'post',
                          onComplete: this.onComplete,
                          onSuccess: this.onSuccess,
                          onFailure: this.onFailure
                        }
        );
      }
      else {
        this.form.show();
      }
    }
  },
          
  showLoader: function(container) {
    var loader = new Element('div', {id: 'simplecheckout-ajax-loader'});
    container.insert({
      top: loader.update('Loading, please wait...')
    });
    loader.setStyle({'line-height': loader.getHeight()+'px'});
  },
          
  insertForm: function(transport) {
    var response = this._getJsonResponse(transport);
    this.loginEnabled = true;
    $$('body')[0].insert(response.html);
    this.form = $('loginformOverlay');
    
    var block = this.form.select('.blocks-container').first();
    var viewport = document.viewport.getHeight();
    var top = (viewport - block.getHeight()) / 2;
    block.setStyle({marginTop: top + 'px'});
  },
          
  showRegisterForm: function(){
    $('simplecheckout-login-form').hide();
    $('simplecheckout-register-form').show();
  },
  showLoginForm: function(){    
    $('simplecheckout-register-form').hide();
    $('simplecheckout-forgotpassword-form').hide();
    $('simplecheckout-login-form').show();
  },
  showForgotPasswordForm: function() {
    $('simplecheckout-login-form').hide();
    $('simplecheckout-forgotpassword-form').show();
  },
          
  hide: function() {
    this.loginEnabled = false;
    this.showLoginForm();
    this.form.remove();
  },
  resetLoadWaiting: function(transport) {
    $('simplecheckout-ajax-loader').remove();
  },
  alertFailure: function(transport) {},
  
  login: function() {
    var loginForm = $('login-form');
    var validator = new Validation(loginForm, {addClassNameToContainer: true});
    
    if (validator.validate()) {
      if ($('login-form-errors') !== null) {
        $('login-form-errors').remove();
      }
      this.showLoader($$('#simplecheckout-login-form .registered-users .buttons-set')[0]);
      new Ajax.Request(loginForm.getAttribute('action'),
                      {
                        method: 'post',
                        onComplete: this.onComplete,
                        onSuccess: this.onLogin,
                        onFailure: this.onFailure,
                        parameters: Form.serialize(loginForm)
                      });
    }
  },
          
  parseLoginResponse: function(transport) {
    var response = this._getJsonResponse(transport);
    this.parseStepResponse(response, $('login-form')); 
  },
          
  register: function() {
    var registerForm = $('register-form');
    var validator = new Validation(registerForm, {addClassNameToContainer: true});
    
    if (validator.validate()) {
      if ($('login-form-errors') !== null) {
        $('login-form-errors').remove();
      }
      this.showLoader($$('#simplecheckout-register-form .buttons-set')[0]);
      new Ajax.Request(registerForm.getAttribute('action'),
                      {
                        method: 'post',
                        onComplete: this.onComplete,
                        onSuccess: this.onRegister,
                        onFailure: this.onFailure,
                        parameters: Form.serialize(registerForm)
                      });
    }
  },
          
  parseRegisterResponse: function(transport) {
    var response = this._getJsonResponse(transport);
    this.parseStepResponse(response, $('register-form'));
  },
          
  rememberPassword: function() {
    var rememberForm = $('forgotpassword-form');
    var validator = new Validation(rememberForm, {addClassNameToContainer: true});
    
    if (validator.validate()) {
      if ($('login-form-errors') !== null) {
        $('login-form-errors').remove();
      }
      this.showLoader($$('#simplecheckout-forgotpassword-form .buttons-set')[0]);
      new Ajax.Request(rememberForm.getAttribute('action'),
                      {
                        method: 'post',
                        onComplete: this.onComplete,
                        onSuccess: this.onRemember,
                        onFailure: this.onFailure,
                        parameters: Form.serialize(rememberForm)
                      });
    }
  },
  
  parseRememberResponse: function(transport) {
    var response = this._getJsonResponse(transport);
    this.parseStepResponse(response, $('login-form'));
  },
          
  parseStepResponse: function(response, form) {
    var messagesClass = 'error-msg';
    if (response.error === 0) {
      //redirect
      if (response.redirect !== undefined) {
        window.location = response.redirect;
      }
      //change form
      else if (response.show_step !== undefined) {
        var step = response.show_step.charAt(0).toUpperCase() + response.show_step.slice(1);
        var method = 'show' + step + 'Form';
        this[method]();
        messagesClass = 'success-msg';
        if ($('login-form-errors')) {
          $('login-form-errors').remove();
        }
      }
    }   
    //show info
    if (response.message !== undefined) {
      if ($('login-form-errors') === null) {
        var messages = new Element('ul', {"id": 'login-form-errors', "class": 'messages'});
        messages.update('<li class="' + messagesClass + '"><ul></ul></li>');
        form.insert({
          "top": messages
        });        
      }

      var str = '';
      response.message.each(function(m){
        str += '<li>' + m + '</li>';
      });
      $$('#login-form-errors .' + messagesClass + ' ul')[0].update(str);
    }
    
  },
  
  _getJsonResponse: function(transport) {
    var response = null;
    if (!transport.responseJSON) {
      try{
        response = eval('(' + transport.responseText + ')');
      }
      catch (e) {
        response = {};
      }
    }
    else {
      response = transport.responseJSON;
    }
    return response;
  }
};

SimpleCheckout = Class.create();
SimpleCheckout.prototype = {
  initialize: function(firstStep, returnUrl, checkoutType){
    this.showFirstStep(firstStep);
    this.loadWaiting = false;
    this.returnUrl = returnUrl;
    this.checkoutType = checkoutType;
    window.onpopstate = this.onPopState.bindAsEventListener(this);    
    document.observe('dom:loaded',function(){
      this.updateTitles();  
    }.bind(this));
  },
          
  onPopState: function(event) {
    if (event.state !== null && event.state.step !== null) {
      this.returnToStep(event.state.step);
    }
  },
          
 showFirstStep: function(step) {
   if (step === null) {
    step = $$('ul.steps > li').first().getAttribute('id').replace('step-','');
   }
   else {
     var  self = this;
     $$('ul.steps > li').each(function(elem, idx) {
       var s = elem.getAttribute('id').replace('step-','');
       if (s !== step) {
         self.setStepCompleted(s);
       }
       else {
         throw $break;
       }
     });
   }
   this.showStep(step);
   history.replaceState({step: step}, step, document.location.href);
    //$$('ul.steps > li').invoke('addClassName','open');
 },
  
  showStep: function(step) {
    $$('ul.steps > li').invoke('removeClassName','open');
    $$('ul.steps > li').invoke('addClassName','close');
    $('step-' + step).removeClassName('close').addClassName('open');
  },
          
  setStepCompleted: function(step) {
    $('step-' + step).removeClassName('open').addClassName('completed');
  },
          
  returnToStep: function(step) {
    
    //change history state
    var link = document.location.href;
    var newlink = link.substring(0, link.indexOf(this.checkoutType + '/') + 2) + step;
    history.pushState({step: step}, step, newlink);    
    //show step
    var stepId = 'step-' + step;
    var prevStep = true;
    $$('ul.steps > li').each(function(elem){
      if (elem.getAttribute('id') === stepId) {
        elem.removeClassName('completed').removeClassName('close').addClassName('open');
        prevStep = false;
      }
      else if (prevStep === false) {
        elem.removeClassName('completed').removeClassName('open').addClassName('close');
      }
      else if (prevStep === true) {
        elem.addClassName('completed').removeClassName('open').addClassName('close');
      }
    });

    this.updateTitles();
  },
          
  setStepResponse: function(response, step) {
    if (response.error !== undefined) {
      alert(response.message);
    }
    else {
      this.setStepCompleted(step);
    }
    if (response.section_html !== undefined && response.section_html !== '') {
      for (section_name in response.section_html) {
        this._updateSection(section_name, response.section_html[section_name]);
      }
    }
    if (response.goto_section !== undefined) {
      this.showStep(response.goto_section);
      if (response.navigator_state !== undefined) {
        history.pushState({step: response.goto_section}, step, response.navigator_state);      
      }
    }
    
    if (response.success !== undefined && response.success === true && response.redirect !== undefined) {
      window.location = response.redirect;
    }
    
    this.updateTitles();
    beautifyCheckbox();
  },

  ajaxFailure: function() {
    alert('There was an error performing the request. Please try again later.');
    this.setLoadWaiting(false);
  },
          
  setLoadWaiting: function(flag) {
    
    if (flag !== false) {
      //show loading box
      if ($('step-' + flag)) {
        var elem = $('step-' + flag);
        elem.addClassName('loading');
        this.showAjaxLoader(elem.select('.step-frame-wrapper').first());
      }
    }
    else {
      $('step-' + this.loadWaiting).removeClassName('loading');
      this.hideAjaxLoader();
    }
    this.loadWaiting = flag;
  },
          
  showAjaxLoader: function(container) {
    var loader = $('ajax-loader');
    var loaderContainer = $('loader-container');
    //calculate position - middle of the container
    var position = container.positionedOffset();
    
    loaderContainer.show();
    loaderContainer.setStyle({
      position: 'absolute',
      width: container.getWidth() + 'px',
      height: container.getHeight() + 'px',
      top: position.top + 'px',
      left: position.left + 'px',
      'z-index': 99999
    });
    var margin = Math.ceil(loaderContainer.getHeight() / 2) - Math.floor(loader.getHeight() / 2);
    loader.setStyle({
      marginTop: margin + 'px'
    });
  },
          
  hideAjaxLoader: function() {
    $('loader-container').setStyle({
      display: 'none',
      position: 'relative',
      width: 'auto',
      height: 'auto',
      top: '0px',
      left: '0px'
    });
  },
          
  _updateSection: function(section, html) {
    $('step-' + section).select('div.step-form').first().update(html);
  },
          
  getActiveStep: function() {
    return $$('.steps li.open').first();
  },
          
  updateTitles: function() {        
    shipping.updateTitle(null);
    payment.updateTitle(null);
  }
};

SimpleShipping= Class.create();
SimpleShipping.prototype = {
  initialize: function(form, saveUrl, shippingPriceUrl){
    this.form = $(form);
    this.shippingPriceUrl = shippingPriceUrl;
    this.saveUrl = saveUrl;
    this.onSave = this.nextStep.bindAsEventListener(this);    
    this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);    
    this.onShippingPriceSuccess = this.updateShippingPrice.bindAsEventListener(this);
    this.onShippingPriceComplete = this.resetPriceRequest.bindAsEventListener(this);
    this.shouldValidate = false;
    this.bindCountryChange();
    this.togglePhoneField($('shipping:country_id'));
    this.bindShippingPrice();
    this.shippingPriceRequest = null;
  },
          
  bindShippingPrice: function() {
    var elements = this.form.select('[id="shipping-address-select"], [name="shipping\[country_id\]"], [name="shipping\[postcode\]"]');
    var self = this;
    elements.invoke('observe','change', function(event){
      self.reloadShippingPrice(event);
    });
  },
          
  reloadShippingPrice: function(event) {
    if (this.shippingPriceRequest !== null) {
      //stop previous not finished request
      this.shippingPriceRequest.stop();
    }
    //calculate new price only when there are country and zipcode
    if (($('shipping-address-select') == null || (/*$('shipping-address-select').value !== "" &&*/ $('shipping-address-select').value !== "placeholder")) 
        && ($('shipping:postcode').value !== "" && $('shipping:country_id').value !== "" && $('shipping:country_id').value !== "US")) {
      $('shipping-price-recalculating').show();
      $('shipping-estimate').hide();
      this.shippingPriceRequest = new Ajax.Request(this.shippingPriceUrl,
                        {
                          method: 'post',
                          onSuccess: this.onShippingPriceSuccess,
                          onComplete: this.onShippingPriceComplete,
                          onFailure: checkout.ajaxFailure.bind(checkout),
                          parameters: Form.serialize(this.form)
                        }
      );
    }
    else {
      $('shipping-estimate').hide();
    }
  },
          
  resetPriceRequest: function() {
    this.shippingPriceRequest = null;
  },
          
  updateShippingPrice: function(transport) {
    var response = transport.responseJSON;    
    $$('.shipping-estimate .caption')[0].update(response.country);
    $$('.shipping-estimate .shipping_cost')[0].update(response.price);
    $('shipping-price-recalculating').hide();
    $('shipping-estimate').show();
  },
          
  bindCountryChange: function() {
    var self = this;
    $('shipping:country_id').observe('change', function() {
      self.togglePhoneField(this);
    });
  },
          
  togglePhoneField: function(element) {       
      if (element.value === 'US') {
        $('phone-field').hide();
      } else {
        $('phone-field').show();
      }
  },
          
  updateTitle: function(text) {
    if (text === null) {      
      if (checkout.getActiveStep().readAttribute('id') === 'step-shipping') {
        text = $$('#co-shipping-form .current-address-wrapper').length > 0 ? 'Shipping To:' : 'Enter Shipping Address';
      }
      else {
        text = 'Shipping Address';
      }
    }    
    $('step-shipping').select('h2 span.step-title').first().update(text);
    
  },
          
  newAddress: function(show) {
    if (show) {      
      this.updateTitle('Enter Shipping Address');
      if ($$('#step-shipping .current-address-wrapper').length > 0){
        $$('#step-shipping .current-address-wrapper').first().hide();
      }
      $$('.address-changer').first().hide();
      $('shipping-new-address-form').show();
      $('save-address').show();
      this.shouldValidate = true;
    }
    else {
      $('shipping-new-address-form').hide();
      $('save-address').hide();      
      this.shouldValidate = false;
    }
  },
          
  save: function(){    
    if (checkout.loadWaiting !== false) return;
    var ajax = true;    
    if (this.shouldValidate) {
      var validator = new Validation(this.form, {addClassNameToContainer: true});  
      ajax = validator.validate();      
    }
    if (ajax) {
      checkout.setLoadWaiting('shipping');
      var request = new Ajax.Request(this.saveUrl,
                        {
                          method: 'post',
                          onComplete: this.onComplete,
                          onSuccess: this.onSave,
                          onFailure: checkout.ajaxFailure.bind(checkout),
                          parameters: Form.serialize(this.form)
                        }
      );
    }
  },
          
  nextStep: function(transport){
    var response = null;
    if (!transport.responseJSON) {
      try{
        response = eval('(' + transport.responseText + ')');
      }
      catch (e) {
        response = {};
      }
    }
    else {
      response = transport.responseJSON;
    }
    checkout.setStepResponse(response, 'shipping');    
  },

  resetLoadWaiting: function(transport){
    checkout.setLoadWaiting(false);    
  }
};


SimplePayment = Class.create();
SimplePayment.prototype = {
  beforeInitFunc:$H({}),
  afterInitFunc:$H({}),
  beforeValidateFunc:$H({}),
  afterValidateFunc:$H({}),
    
  initialize: function(form, saveUrl){
    this.beforeInit();
    this.form = $(form);
    this.saveUrl = saveUrl;
    this.onSave = this.nextStep.bindAsEventListener(this);
    this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    //this.lastUsedMethod = null;
    this._enableSelectedMethod();
    this.afterInit();
  },
          
  updateTitle: function(text) {
    if (text === null) {      
      if (checkout.getActiveStep().readAttribute('id') === 'step-payment') {
        text = 'Enter Payment Information';
      }
      else {
        text = 'Payment Information';
      }
    }    
    $('step-payment').select('h2 span.step-title').first().update(text);
    
  },
  
  addBeforeInitFunction: function(code, func) {
    this.beforeInitFunc.set(code, func);
  },
  beforeInit: function() {
    (this.beforeInitFunc).each(function(init) {
      (init.value)();
      ;
    });
  },
  addAfterInitFunction: function(code, func) {
    this.afterInitFunc.set(code, func);
  },
  afterInit: function() {
    (this.afterInitFunc).each(function(init) {
      (init.value)();
    });
  },
          
  switchMethod: function(method) {
    if (method) {
      $$('input[name="payment[method]"][value="'+method+'"]').first().checked = true; //IE8
      //hide previous method
      $$('.payment-methods').invoke('removeClassName','selected');    
      $$('.step-payment .form-list').invoke('hide');

      //select method
      $('method_' + method).addClassName('selected');
      //disable current payment method form
      this.changeVisible(this.currentMethod, true);
      //show payment form
      if ($('payment_form_' + method)) {
        $('payment_form_' + method).show();
        //enable current method form
        this.changeVisible(method, false);
      }
      this.lastUsedMethod = method;      
    }
    this.currentMethod = method;
        
  },
          
  changeVisible: function(method, mode) {
    var block = 'payment_form_' + method;
    [block].each(function(el) {
      element = $(el);
      if (element) {
        element.style.display = (mode) ? 'none' : '';
        element.select('input', 'select', 'textarea', 'button').each(function(field) {
          field.disabled = mode;
        });
      }
    });
  },
          
  save: function(){
    if (checkout.loadWaiting !== false) return;
    var validator = new Validation(this.form, {addClassNameToContainer: true});
    if (this.validate() && validator.validate()) {
      checkout.setLoadWaiting('payment');
      var request = new Ajax.Request(this.saveUrl,
                        {
                          method: 'post',
                          onComplete: this.onComplete,
                          onSuccess: this.onSave,
                          onFailure: checkout.ajaxFailure.bind(checkout),
                          parameters: Form.serialize(this.form)
                        }
      );
    }
  },
          
  addBeforeValidateFunction : function(code, func) {
    this.beforeValidateFunc.set(code, func);
  },

  beforeValidate : function() {
    var validateResult = true;
    var hasValidation = false;
    (this.beforeValidateFunc).each(function(validate){
      hasValidation = true;
      if ((validate.value)() === false) {
        validateResult = false;
      }
    }.bind(this));
    if (!hasValidation) {
        validateResult = false;
    }
    return validateResult;
  },

  validate: function() {
    var result = this.beforeValidate();
    if (result) {
      return true;
    }
    var methods = document.getElementsByName('payment[method]');
    if (methods.length === 0) {
      alert(Translator.translate('Your order cannot be completed at this time as there is no payment methods available for it.').stripTags());
      return false;
    }
    for (var i=0; i<methods.length; i++) {
      if (methods[i].checked) {
        return true;
      }
    }
    result = this.afterValidate();
    if (result) {
      return true;
    }
    alert(Translator.translate('Please specify payment method.').stripTags());
    return false;
  },

  addAfterValidateFunction : function(code, func) {
    this.afterValidateFunc.set(code, func);
  },

  afterValidate : function() {
    var validateResult = true;
    var hasValidation = false;
    (this.afterValidateFunc).each(function(validate){
      hasValidation = true;
      if ((validate.value)() === false) {
        validateResult = false;
      }
    }.bind(this));
    if (!hasValidation) {
      validateResult = false;
    }
    return validateResult;
  },
          
  nextStep: function(transport){
    var response = null;
    if (!transport.responseJSON) {
      try{
        response = eval('(' + transport.responseText + ')');
      }
      catch (e) {
        response = {};
      }
    }
    else {
      response = transport.responseJSON;
    }
    checkout.setStepResponse(response, 'payment');
  },
          
  resetLoadWaiting: function(transport){
    checkout.setLoadWaiting(false);    
  },
          
  showBillingAddress: function() {  
    $('billing-address-changer').toggle();
  },
          
  newAddress: function(flag){
    if (flag) {
      $('billing-new-address-form').show();
    }
    else {
      $('billing-new-address-form').hide();
    }
  },
  
  _enableSelectedMethod: function() {
    methods = document.getElementsByName('payment[method]');
    for (var i=0; i<methods.length; i++) {
      if (methods[i].checked) {
        this.switchMethod(methods[i].value);
        return true;
      }
    }    
  }
};

SimpleReview = Class.create();
SimpleReview.prototype = {
  initialize: function(saveUrl){
    this.saveUrl = saveUrl;
    this.onSave = this.nextStep.bindAsEventListener(this);
    this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    this._bindProducts();
  },
          
  _bindProducts: function() {
    $$('.step-review .data-table .expandable').invoke('observe', 'click', function() {
      var elem = $(this);
      elem.up('td').toggleClassName('expanded');
      elem.up('tr').next().toggle();
    });
  },
          
  save: function() {
    if (checkout.loadWaiting !== false) return;
    checkout.setLoadWaiting('review');
    var params = Form.serialize(payment.form);
    params.save = true;
    var request = new Ajax.Request(this.saveUrl,
                        {
                          method:'post',
                          parameters:params,
                          onComplete: this.onComplete,
                          onSuccess: this.onSave,
                          onFailure: checkout.ajaxFailure.bind(checkout)
                        }
    );
  },
          
  nextStep: function(transport){
    var response = null;
    if (!transport.responseJSON) {
      try{
        response = eval('(' + transport.responseText + ')');
      }
      catch (e) {
        response = {};
      }
    }
    else {
      response = transport.responseJSON;
    }
    checkout.setStepResponse(response, 'review');
  },
          
  resetLoadWaiting: function(transport){
    checkout.setLoadWaiting(false);    
  }
};
