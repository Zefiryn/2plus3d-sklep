Checkout.prototype.initialize = function(accordion, urls) {
  this.accordion = accordion;
  this.progressUrl = urls.progress;
  this.reviewUrl = urls.review;
  this.saveMethodUrl = urls.saveMethod;
  this.failureUrl = urls.failure;
  this.billingForm = false;
  this.shippingForm = false;
  this.syncBillingShipping = false;
  this.method = '';
  this.payment = '';
  this.loadWaiting = false;
  this.showShippingAddress = urls.showShippingAddress;
  this.steps = ['billing', 'shipping', 'payment', 'review'];

  this.accordion.sections.each(function(section) {
    Event.observe($(section).down('.step-title'), 'click', this._onSectionClick.bindAsEventListener(this));
  }.bind(this));

  this.accordion.disallowAccessToNextSections = true;
};

Checkout.prototype.setActiveStep = function() {

  $$('.opc-block-progress li.active').invoke('removeClassName', 'active');
  var id = $$('.opc li.active')[0].readAttribute('id');
  var active = false;  
  this.steps.each(function(step){
    if ('opc-' + step === id) {
      active = true;
      $(step + '-progress-opcheckout').addClassName('active');
    }
    if (active === false) {
      if ($(step + '-progress-opcheckout') != null) {
        $(step + '-progress-opcheckout').addClassName('completed');
      }
    }
    else {
      if ($(step + '-progress-opcheckout') != null) {
        $(step + '-progress-opcheckout').removeClassName('completed');
      }
    }
  });
};
        
Checkout.prototype.reloadStep = function(prevStep) {
  var updater = new Ajax.Updater(prevStep + '-progress-opcheckout', this.progressUrl, {
    method: 'get',
    evalScripts: true,
    onFailure: this.ajaxFailure.bind(this),
    onComplete: function() {
      this.checkout.resetPreviousSteps();
    },
    parameters: prevStep ? {prevStep: prevStep} : null
  });
};

Checkout.prototype.gotoSection = function(section)
{        
  if (section != 'shipping_method') {
    this.reloadProgressBlock(this.currentStep);
    this.currentStep = section;
    var sectionElement = $('opc-' + section);
    sectionElement.addClassName('allow');
    this.accordion.openSection('opc-' + section);
    this.setActiveStep();    
  }
  else {
    $('shipping-method-continue-btn').click();  
  }  
};

Checkout.prototype.back = function() {
  if (this.loadWaiting) return;
  var currentStep = $$('.opc li.section.active')[0];
  var step = currentStep.readAttribute('id').replace('opc-', '');
  var go = this.steps[this.steps.indexOf(step) - 1];
  this.gotoSection(go);
};

Checkout.prototype.setStepResponse = function(response) {
  if (response.update_section) {
    $('checkout-' + response.update_section.name + '-load').update(response.update_section.html);
  }
  if (response.allow_sections) {
    response.allow_sections.each(function(e) {
      $('opc-' + e).addClassName('allow');
    });
  }
  if (response.duplicateBillingInfo)
  {
    this.syncBillingShipping = true;
    shipping.setSameAsBilling(true);
  }
  if (response.goto_section) {    
    this.gotoSection(response.goto_section, true);
    return true;
  }
  if (response.redirect) {
    location.href = response.redirect;
    return true;
  }
  return false;
};


Billing.prototype.newAddress = function(isNew) {
  if (isNew) {
    this.resetSelectedAddress();
    Element.show('billing-new-address-form');
    $$('.shipping_address_settings').invoke('removeClassName', 'no-indent');
  } else {
    Element.hide('billing-new-address-form');
    $$('.shipping_address_settings').invoke('addClassName', 'no-indent');
  }
};

Billing.prototype.save = function() {
  if (checkout.loadWaiting != false) return;

  var validator = new Validation(this.form);
  if (validator.validate()) {
    checkout.setLoadWaiting('billing');
    $('billing:require_invoice').checked ? checkout.showShippingAddress = true : checkout.showShippingAddress = false;
    var request = new Ajax.Request(
            this.saveUrl,
            {
              method: 'post',
              onComplete: this.onComplete,
              onSuccess: this.onSave,
              onFailure: checkout.ajaxFailure.bind(checkout),
              parameters: Form.serialize(this.form)
            }
    );
  }
};

Billing.prototype.toggleInvoiceFields = function(input) {
  if (input.checked == true) {
    this.showInvoiceFields();
  }
  else {
    this.hideInvoiceFields();
  }
};

Billing.prototype.showInvoiceFields = function() {
  $('invoice-fields').show();
  $$('#invoice-fields label').invoke('addClassName', 'required');
  $$('#invoice-fields input').invoke('addClassName', 'required-entry');
};

Billing.prototype.hideInvoiceFields = function() {
  $$('#invoice-fields label').invoke('removeClassName', 'required');
  $$('#invoice-fields input').invoke('removeClassName', 'required-entry');
  $('billing:company').value = null;
  $('billing:vat_id').value = null;
  $('invoice-fields').hide();
};

Shipping.prototype.newAddress = function(isNew) {
  if (isNew) {
    this.resetSelectedAddress();
    Element.show('shipping-new-address-form');
    $$('.use-billing-address').invoke('removeClassName', 'no-indent');
  } else {
    Element.hide('shipping-new-address-form');
    $$('.use-billing-address').invoke('addClassName', 'no-indent');
  }
  shipping.setSameAsBilling(false);
};

Shipping.prototype.syncWithBilling = function() {
  $('billing-address-select') && this.newAddress(!$('billing-address-select').value);
  $('shipping:same_as_billing').checked = true;
  if (!$('billing-address-select') || !$('billing-address-select').value) {
    arrElements = Form.getElements(this.form);
    for (var elemIndex in arrElements) {
      if (arrElements[elemIndex].id) {
        var sourceField = $(arrElements[elemIndex].id.replace(/^shipping:/, 'billing:'));
        if (sourceField) {
          arrElements[elemIndex].value = sourceField.value;
        }
      }
    }    
  } else {
    $('shipping-address-select').value = $('billing-address-select').value;
  }
};