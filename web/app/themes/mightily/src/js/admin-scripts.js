

    var fields = [
        '#title',
        '#product-type',
        '#_virtual',
        '#_downloadable',
        '#_regular_price',
        '#_sale_price',
        '#_tax_status',
        '#_tax_class',
        '#_sku',
        '#_manage_stock',
        '#_stock',
        '#_backorders',
        '#_low_stock_amount',
        '#_sold_individually',
        '#_weight',
        '.dimensions_field .wc_input_decimal',
        '#product_shipping_class',
        '[data-name="isbn_10"] input',
        '[data-name="isbn_13"] input',
        '[data-name="publication_date"] input',
    ];

    var autocompleteBilling, autocompleteShipping;

    function initAutocomplete() {
      // Set up autocomplete for the billing fields
      if(document.getElementById('_billing_address_1')){
        if(!document.getElementById('_billing_address_1').hasAttribute('readonly')){
          autocompleteBilling = new google.maps.places.Autocomplete(
            document.getElementById('_billing_address_1'), {types: ['geocode']}
          );  
          autocompleteBilling.setFields(['address_component']);
          autocompleteBilling.addListener('place_changed', fillBillingAddress);  
          document.getElementById('_billing_address_1').setAttribute('autocomplete', 'false');
          document.getElementById('_billing_address_1').setAttribute('onfocus', 'geolocate()');
        }
      }
      // Set up autocomplete for the shipping fields
      if(document.getElementById('_shipping_address_1')){
        autocompleteShipping = new google.maps.places.Autocomplete(
          document.getElementById('_shipping_address_1'), {types: ['geocode']}
        );  
        autocompleteShipping.setFields(['address_component']);
        autocompleteShipping.addListener('place_changed', fillShippingAddress);  
        document.getElementById('_shipping_address_1').setAttribute('autocomplete', 'false');
        document.getElementById('_shipping_address_1').setAttribute('onfocus', 'geolocate()');
      }  
    }
    
    function fillBillingAddress() {
      // Get the place details from the autocomplete object.
      var place = autocompleteBilling.getPlace();
    
      document.getElementById('_billing_address_1').value = '';
      document.getElementById('_billing_address_2').value = '';
      document.getElementById('_billing_city').value = '';
      document.getElementById('_billing_state').value = '';
      document.getElementById('_billing_postcode').value = '';
      document.getElementById('_billing_country').value = '';
    
      // Get each component of the address from the place details,
      // and then fill-in the corresponding field on the form.
      for (var i = 0; i < place.address_components.length; i++) {
        var addressType = place.address_components[i].types[0];
        console.log(place.address_components[i].types[0]);  
        if(addressType == 'street_number'){
            document.getElementById('_billing_address_1').value = place.address_components[i]['short_name'] + ' ';
        }
        if(addressType == 'route'){
            document.getElementById('_billing_address_1').value = document.getElementById('_billing_address_1').value + place.address_components[i]['long_name'];
        }
        if(addressType == 'locality'){
            document.getElementById('_billing_city').value = place.address_components[i]['long_name'];
        }
        if(addressType == 'postal_code'){
            document.getElementById('_billing_postcode').value = place.address_components[i]['short_name'];  
        }    
        if(addressType == 'country'){
            document.getElementById('_billing_country').value = place.address_components[i]['short_name'];  
            jQuery('#_billing_country').trigger('change.select2');
            jQuery('#_billing_country').trigger('change');
            jQuery('#_billing_country').trigger('click');        
        }
        if(addressType == 'administrative_area_level_1'){
          var tempI = i;
          setTimeout(function(){
            document.getElementById('_billing_state').value = place.address_components[tempI]['short_name'];
            jQuery('#_billing_state').trigger('change.select2');
            jQuery('#_billing_state').trigger('change');
            jQuery('#_billing_state').trigger('click');
          }, 500);
        }
      }
    }
    
    function fillShippingAddress() {
      // Get the place details from the autocomplete object.
      var place = autocompleteShipping.getPlace();
    
      document.getElementById('_shipping_address_1').value = '';
      document.getElementById('_shipping_address_2').value = '';
      document.getElementById('_shipping_city').value = '';
      document.getElementById('_shipping_state').value = '';
      document.getElementById('_shipping_postcode').value = '';
      document.getElementById('_shipping_country').value = '';
    
      // Get each component of the address from the place details,
      // and then fill-in the corresponding field on the form.
      for (var i = 0; i < place.address_components.length; i++) {
        var addressType = place.address_components[i].types[0];
        if(addressType == 'street_number'){
            document.getElementById('_shipping_address_1').value = place.address_components[i]['short_name'] + ' ';
        }
        if(addressType == 'route'){
            document.getElementById('_shipping_address_1').value = document.getElementById('_shipping_address_1').value + place.address_components[i]['long_name'];
        }
        if(addressType == 'locality'){
            document.getElementById('_shipping_city').value = place.address_components[i]['long_name'];
        }
        if(addressType == 'postal_code'){
            document.getElementById('_shipping_postcode').value = place.address_components[i]['short_name'];  
        }    
        if(addressType == 'country'){
            document.getElementById('_shipping_country').value = place.address_components[i]['short_name'];
            jQuery('#_shipping_country').trigger('change.select2');
            jQuery('#_shipping_country').trigger('change');
            jQuery('#_shipping_country').trigger('click');        
        }
        if(addressType == 'administrative_area_level_1'){
          var tempI = i;
          setTimeout(function(){
            document.getElementById('_shipping_state').value = place.address_components[tempI]['short_name'];
            jQuery('#_shipping_state').trigger('change.select2');
            jQuery('#_shipping_state').trigger('change');
            jQuery('#_shipping_state').trigger('click');
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

    initAutocomplete();


