var autocompleteBilling, autocompleteShipping;


function initAutocomplete() {
  // Set up autocomplete for the billing fields
  if(document.getElementById('billing_address_1')){
    if(!document.getElementById('billing_address_1').hasAttribute('readonly')){
      autocompleteBilling = new google.maps.places.Autocomplete(
        document.getElementById('billing_address_1'), {types: ['geocode']}
      );  
      autocompleteBilling.setFields(['address_component']);
      autocompleteBilling.addListener('place_changed', fillBillingAddress);  
      document.getElementById('billing_address_1').setAttribute('autocomplete', 'false');
      document.getElementById('billing_address_1').setAttribute('onfocus', 'geolocate()');
    }
  }
  // Set up autocomplete for the shipping fields
  if(document.getElementById('shipping_address_1')){
    autocompleteShipping = new google.maps.places.Autocomplete(
      document.getElementById('shipping_address_1'), {types: ['geocode']}
    );  
    autocompleteShipping.setFields(['address_component']);
    autocompleteShipping.addListener('place_changed', fillShippingAddress);  
    document.getElementById('shipping_address_1').setAttribute('autocomplete', 'false');
    document.getElementById('shipping_address_1').setAttribute('onfocus', 'geolocate()');
  }  
}

function fillBillingAddress() {
  // Get the place details from the autocomplete object.
  var place = autocompleteBilling.getPlace();

  document.getElementById('billing_address_1').value = '';
  document.getElementById('billing_address_2').value = '';
  document.getElementById('billing_city').value = '';
  document.getElementById('billing_state').value = '';
  document.getElementById('billing_postcode').value = '';
  document.getElementById('billing_country').value = '';

  // Get each component of the address from the place details,
  // and then fill-in the corresponding field on the form.
  for (var i = 0; i < place.address_components.length; i++) {
    var addressType = place.address_components[i].types[0];
    console.log(place.address_components[i].types[0]);  
    if(addressType == 'street_number'){
        document.getElementById('billing_address_1').value = place.address_components[i]['short_name'] + ' ';
    }
    if(addressType == 'route'){
        document.getElementById('billing_address_1').value = document.getElementById('billing_address_1').value + place.address_components[i]['long_name'];
    }
    if(addressType == 'locality'){
        document.getElementById('billing_city').value = place.address_components[i]['long_name'];
    }
    if(addressType == 'postal_code'){
        document.getElementById('billing_postcode').value = place.address_components[i]['short_name'];  
    }    
    if(addressType == 'country'){
        document.getElementById('billing_country').value = place.address_components[i]['short_name'];  
        $('#billing_country').trigger('change.select2');
        $('#billing_country').trigger('change');
        $('#billing_country').trigger('click');        
    }
    if(addressType == 'administrative_area_level_1'){
      var tempI = i;
      setTimeout(function(){
        document.getElementById('billing_state').value = place.address_components[tempI]['short_name'];
        $('#billing_state').trigger('change.select2');
        $('#billing_state').trigger('change');
        $('#billing_state').trigger('click');
      }, 500);
    }
    
  }
  jQuery('body').trigger('update_checkout');
}

function fillShippingAddress() {
  // Get the place details from the autocomplete object.
  var place = autocompleteShipping.getPlace();

  document.getElementById('shipping_address_1').value = '';
  document.getElementById('shipping_address_2').value = '';
  document.getElementById('shipping_city').value = '';
  document.getElementById('shipping_state').value = '';
  document.getElementById('shipping_postcode').value = '';
  document.getElementById('shipping_country').value = '';

  // Get each component of the address from the place details,
  // and then fill-in the corresponding field on the form.
  for (var i = 0; i < place.address_components.length; i++) {
    var addressType = place.address_components[i].types[0];
    if(addressType == 'street_number'){
        document.getElementById('shipping_address_1').value = place.address_components[i]['short_name'] + ' ';
    }
    if(addressType == 'route'){
        document.getElementById('shipping_address_1').value = document.getElementById('shipping_address_1').value + place.address_components[i]['long_name'];
    }
    if(addressType == 'locality'){
        document.getElementById('shipping_city').value = place.address_components[i]['long_name'];
    }
    if(addressType == 'postal_code'){
        document.getElementById('shipping_postcode').value = place.address_components[i]['short_name'];  
    }    
    if(addressType == 'country'){
        document.getElementById('shipping_country').value = place.address_components[i]['short_name'];
        $('#shipping_country').trigger('change.select2');
        $('#shipping_country').trigger('change');
        $('#shipping_country').trigger('click');        
    }
    if(addressType == 'administrative_area_level_1'){
      var tempI = i;
      setTimeout(function(){
        document.getElementById('shipping_state').value = place.address_components[tempI]['short_name'];
        $('#shipping_state').trigger('change.select2');
        $('#shipping_state').trigger('change');
        $('#shipping_state').trigger('click');
      }, 500);      
    }    
  }
  jQuery('body').trigger('update_checkout');
}

// Bias the autocomplete object to the user's geographical location,
// as supplied by the browser's 'navigator.geolocation' object.
function geolocate() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(position) {
      var geolocation = {
        lat: position.coords.latitude,
        lng: position.coords.longitude
      };
      var circle = new google.maps.Circle(
          {center: geolocation, radius: position.coords.accuracy});
      if(autocompleteBilling){
        autocompleteBilling.setBounds(circle.getBounds());
      }
      if(autocompleteShipping){
        autocompleteShipping.setBounds(circle.getBounds());
      }
    });
  }
}