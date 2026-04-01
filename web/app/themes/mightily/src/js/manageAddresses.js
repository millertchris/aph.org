Vue.use(window['carbon-vue'].default);

var internalData = addresses_ajax_obj.internalData.l10n_print_after;

var internalDataCombo = internalData.map(function (item, index) {
  var name = 'name';
  var label = '(' + item.group + ', ' + item.fq_account + ') ' + item.first_name + ' ' + item.last_name + ', ' + item.street_address_1 + ', ' + item.street_address_2 + ', ' + item.city + ', ' + item.state + ' ' + item.postal_code;
  var value = item.id;
  return {
    name: name,
    label: label,
    value: value,
  };
});

var tableApp = {};
var comboApp = {};
var addressSaved = false;

function save_address_reset() {
  if (addressSaved) {
    $('#save_address').html('Save this Address');
    addressSaved = false;
  }
}

function save_address_listeners() {
  $('#shipping_first_name, #shipping_last_name, #shipping_company, #shipping_address_1, #shipping_address_2, #shipping_city, #shipping_state, #shipping_postcode, #shipping_country, #shipping_phone').change(function () {
    save_address_reset();
  });
}

function save_address_checkout(event) {
  event = event || window.event;
  event.preventDefault();
  if (addressSaved) {
    return false;
  }
  // Show the loader icon
  $('#save_address_loader').show();
  // Append this new address data to the internalData array
  internalData.push({
    group: '',
    fq_account: '',
    first_name: $('#shipping_first_name').val(),
    last_name: $('#shipping_last_name').val(),
    company: $('#shipping_company').val(),
    street_address_1: $('#shipping_address_1').val(),
    street_address_2: $('#shipping_address_2').val(),
    city: $('#shipping_city').val(),
    state: $('#shipping_state').val(),
    postal_code: $('#shipping_postcode').val(),
    country: $('#shipping_country').val(),
    phone: $('#shipping_phone').val(),
    id: internalData.length + 1
  });
  var address_json = JSON.stringify(internalData);
  $.ajax({
    type: 'POST',
    url: addresses_ajax_obj.ajaxurl, // or ajax_obj.ajaxurl if using on frontend
    data: {
      'action': 'addresses',
      'security': addresses_ajax_obj.addresses_ajax_nonce,
      'address_data': address_json
    },
    success: function (data) {
      //console.log(data);
      addressSaved = true;
      $('#save_address').html('Address Saved <i class="fa fa-check"></i>');
      $('#save_address_loader').hide();
    },
    error: function (errorThrown) {
      //console.log(errorThrown);
    },
    complete: function () {
      // console.log('complete');
    }
  });
}

jQuery(document).ready(function () {

  if (document.getElementById('addresses-app')) {
    var addressApp = new Vue({
      el: '#addresses-app',
      template: '#addresses-template',
      data: function () {
        return {
          yourName: '',
          notify: false,
          dataSelected: 0
        };
      },
      computed: {
        hello: function () {
          if (this.yourName.length) {
            return 'Hello' + this.yourName + '!';
          } else {
            return 'Hello Bob!';
          }
        },
        isSelected: function () {
          return function (index) { this.dataSelected === index };
        }
      },
      methods: {
        sayHi: function () {
          alert('Stay' + this.yourName);
        }
      }
    });
  }
  if (document.getElementById('button-app')) {
    var buttonApp = new Vue({
      el: '#button-app',
      template: '#button-template',
      data: function () {
        return {
          open: [false, false, false, false],
        };
      },
      computed: {
      },
      methods: {
        actionChange: function () {

        }
      }
    });
  }
  if (document.getElementById('table-app')) {
    tableApp = new Vue({
      el: '#table-app',
      template: '#table-template',
      data: function () {
        return {
          internalData: internalData,
          uploadData: [],
          uploadFile: false,
          newData: {
            group: '',
            fq_account: '',
            first_name: '',
            last_name: '',
            company: '',
            street_address_1: '',
            street_address_2: '',
            city: '',
            state: 'AL',
            postal_code: '',
            country: 'US',
            phone: '',
            id: 0
          },
          selectedData: {},
          tempData: {},
          tempIndex: 0,
          errors: [],
          filterValue: '',
          rowSelects: [],
          sortBy: undefined,
          start: 0,
          page: 0,
          length: 0,
          loader: false,
          sampleOverflowMenu: ['Edit', 'Download'],
          columns: [
            "Group Name",
            "FQ Account",
            "Name",
            // "Last Name",
            "company",
            "Address",
            //""
          ],
          us_states: addresses_ajax_obj.states.US,
          ca_states: addresses_ajax_obj.states.CA,
          countries: addresses_ajax_obj.countries
        };
      },
      watch: {
        data: function () {
          this.internalData = this.data;
        },
      },
      computed: {
        filteredData: function () {
          var resultData = [];
          if (this.filterValue) {
            var regex = new RegExp(this.filterValue, 'i');
            resultData = this.internalData.filter(function (item) {
              return JSON.stringify(item).search(regex) >= 0;
            });
          } else {
            // Need to return paginated amount of internal data
            resultData = this.internalData;
          }
          return this.paginateData(resultData);
        },
        pagination: function () {
          return {
            numberOfItems: this.internalData.length,
            pageSizes: [5, { value: 10, selected: true }, 50, 100]
          };
        },
        loaderActive: function () {
          return this.loader;
        }
      },
      methods: {
        validateAddress: function (address) {
          this.errors = [];
          // console.log('validating before save');
          if (address.first_name.length > 25) {
            this.errors.push("Your address first name is too long. Please use 25 characters or less.");
          }
          if (address.last_name.length > 25) {
            this.errors.push("Your address last name is too long. Please use 25 characters or less.");
          }          
          if (address.street_address_1.length < 1) {
            this.errors.push("Please enter a Street Address");
          }
          if (address.postal_code.length < 5) {
            this.errors.push("Please enter a valid Zip/Postal Code");
          }

          // Done with field validation. Now return the result. Scroll to top so errors are visible
          if (!this.errors.length) {
            return true;
          } else {
            jQuery('.bx--modal-content').animate({
              scrollTop: 0
            }, 300);
            return false;
          }
        },
        hideLoader: function () {
          this.loader = false;
          setTimeout(function () {
            $('.cv-loading').removeClass('bx--loading-overlay');
            $('.cv-loading').addClass('bx--loading-overlay--stop');
          }, 1500);
        },
        showLoader: function () {
          this.loader = true;
          $('.cv-loading').removeClass('bx--loading-overlay--stop');
          $('.cv-loading').addClass('bx--loading-overlay');
        },
        importCsv: function () {
          var reader = new FileReader();
          var fileData;
          reader.onload = function (e) {
            // console.log(e);
            fileData = e.target.result;
            if (fileData.indexOf('�') > -1) {
              alert("There was an error processing your CSV file. Please check the file contents and try again.");
              tableApp.uploadModalReset();
              return false;
            }
            fileData = fileData.split(/\r?\n/);
            fileData.shift();
            fileData.forEach(function (item, index) {
              item = item.split(',');
              fileData[index] = {};
              fileData[index].group = item[0];
              fileData[index].fq_account = item[1];
              fileData[index].first_name = item[2];
              fileData[index].last_name = item[3];
              fileData[index].company = item[4];
              fileData[index].street_address_1 = item[5];
              fileData[index].street_address_2 = item[6];
              fileData[index].city = item[7];
              fileData[index].state = item[8];
              fileData[index].postal_code = item[9];
              fileData[index].country = item[10];
              fileData[index].phone = item[11];
              fileData[index].id = index + 1;
            });
            // Need to loop through all rows of this.internalData and remove the data.
            // Then loop through every row of fileData and set it to this.internalData.
            // internalData = fileData;
            // Set all objects to empty
            tableApp.internalData.forEach(function (item, index) {
              tableApp.internalData[index] = {};
            });
            // Remove all empty ojbects
            tableApp.internalData = tableApp.internalData.filter(function (obj) {
              return obj.length == 0;
            });
            fileData.forEach(function (item, index) {
              tableApp.internalData[index] = {};
              tableApp.internalData[index].group = item.group;
              tableApp.internalData[index].fq_account = item.fq_account;
              tableApp.internalData[index].first_name = item.first_name;
              tableApp.internalData[index].last_name = item.last_name;
              tableApp.internalData[index].company = item.company;
              tableApp.internalData[index].street_address_1 = item.street_address_1;
              tableApp.internalData[index].street_address_2 = item.street_address_2;
              tableApp.internalData[index].city = item.city;
              tableApp.internalData[index].state = item.state;
              tableApp.internalData[index].postal_code = item.postal_code;
              tableApp.internalData[index].country = item.country;
              tableApp.internalData[index].phone = item.phone;
              tableApp.internalData[index].id = item.id;
            });

            tableApp.$refs.uploadModal.hide();
            tableApp.uploadModalReset();
            tableApp.save();
          };
          // reader.onloadend = function() {
          // console.log(reader.error.message);
          // };
          reader.readAsText(tableApp.uploadFile);
        },
        generateCsv: function (filename, rows) {
          var processRow = function (row) {
            //row = Object.values(row);
            // Reassign data in case more fields are added in the future. Only assign columns we want to see in the csv
            restructuredRow = [
              row.group,
              row.fq_account,
              row.first_name,
              row.last_name,
              row.company,
              row.street_address_1,
              row.street_address_2,
              row.city,
              row.state,
              row.postal_code,
              row.country,
              row.phone
            ];
            var finalVal = '';
            // This loops through each propery of the address
            for (var j = 0; j < restructuredRow.length; j++) {
              var innerValue = !restructuredRow[j] ? '' : restructuredRow[j].toString();
              var result = innerValue.replace(/"/g, '""');
              if (result.search(/("|,|\n)/g) >= 0)
                result = '"' + result + '"';
              if (j > 0)
                finalVal += ',';
              finalVal += result;
            }
            return finalVal + '\n';
          };

          var csvFile = 'Group,FQ Account,First Name,Last Name,Company,Street Address 1, Street Address 2,City,State,Postal Code,Country,Phone' + '\n';
          for (var i = 0; i < rows.length; i++) {
            csvFile += processRow(rows[i]);
          }

          var blob = new Blob([csvFile], { type: 'text/csv;charset=utf-8;' });
          if (navigator.msSaveBlob) { // IE 10+
            navigator.msSaveBlob(blob, filename);
          } else {
            var link = document.createElement("a");
            if (link.download !== undefined) { // feature detection
              // Browsers that support HTML5 download attribute
              var url = URL.createObjectURL(blob);
              link.setAttribute("href", url);
              link.setAttribute("download", filename);
              link.style.visibility = 'hidden';
              document.body.appendChild(link);
              link.click();
              document.body.removeChild(link);
            }
          }
        },
        paginateData: function (data) {
          return data.slice((this.page - 1) * this.length, this.page * this.length);
        },
        onFilter: function (val) {
          this.filterValue = val;
        },
        onSort: function (sortBy) {
          if (sortBy) {
            this.internalData.sort(function (a, b) {
              switch (sortBy.index) {
                case '0':
                  var itemA = a.group;
                  var itemB = b.group;
                  break;
                case '1':
                  var itemA = a.fq_account;
                  var itemB = b.fq_account;
                  break;
                case '2':
                  var itemA = a.last_name;
                  var itemB = b.last_name;
                  break;
                case '3':
                  var itemA = a.company;
                  var itemB = b.company;
                  break;
                case '4':
                  var itemA = a.street_address_1;
                  var itemB = b.street_address_1;
                  break;
              }
              if (sortBy.order === 'descending') {
                return itemB.localeCompare(itemA);
              }
              if (sortBy.order === 'ascending') {
                return itemA.localeCompare(itemB);
              }
              return 0;
            });
          }
        },
        uploadInput: function () {
          // console.log('upload input');
          //this.$refs.uploadFile.remove();
        },
        uploadChange: function (event) {
          this.uploadFile = event.target.files[0];
        },
        actionDownload: function () {
          // console.log('download');
          this.generateCsv('aph_addresses.csv', this.internalData);
        },
        actionOnPagination: function (pagination) {
          this.start = pagination.start;
          this.page = pagination.page;
          this.length = pagination.length;
        },
        onOverflowMenuClick: function () {
          // console.log('overflow menu click')
        },
        actionRowSelectChange: function () {
          // console.log('row selected')
        },
        // Modal Actions
        helpModalShown: function () {
          // console.log('help shown');
        },
        helpModalHidden: function () {
          // console.log('help hidden');
          $(tableApp.$refs.helpModal.$el).removeClass('is-visible');
        },
        newModalShown: function () {
          // console.log('new shown');
        },
        newModalHidden: function () {
          // console.log('new hidden');
          $(tableApp.$refs.newModal.$el).removeClass('is-visible');
        },
        newPrimaryClick: function () {
          // console.log('new primary');
          if (this.validateAddress(this.newData)) {
            // Add an id property to newData
            var newId = 0;
            if (this.internalData.length == 0) {
              newId = 1;
            } else {
              newId = 1 + (this.internalData[this.internalData.length - 1].id)
            }
            // Add newData object to global array
            this.newData.id = newId;
            this.internalData.push(this.newData);
            //this.internalData = internalData;
            this.$refs.newModal.hide();
            this.save();
          }
        },
        newSecondaryClick: function () {
          // console.log('new secondary');
          this.newData = {};
          this.$refs.newModal.hide();
        },
        uploadModalShown: function () {
          // console.log('upload shown');
        },
        uploadModalHidden: function () {
          // console.log('upload hidden');
          $(tableApp.$refs.uploadModal.$el).removeClass('is-visible');
        },
        uploadPrimaryClick: function () {
          // console.log('upload primary');
          if (this.uploadFile) {
            this.importCsv();
          } else {
            alert('You must choose one CSV file to import.');
          }
        },
        uploadSecondaryClick: function () {
          // console.log('upload secondary');
          this.$refs.uploadModal.hide();
          this.uploadModalReset();
        },
        uploadModalReset: function () {
          // Reset the active files
          document.getElementById('uploadFileInput').value = "";
          this.uploadFile = false;
        },
        showEditModal: function (rowIndex) {
          // console.log('showing edit modal');
          // Save this data in our global so we can use it if the user cancels the edit.
          // Push the current data store to our temp object in case the user cancels edit.
          this.tempData = JSON.parse(JSON.stringify(this.filteredData.slice(rowIndex, rowIndex + 1)[0]));
          this.tempIndex = rowIndex;
          this.selectedData = this.filteredData[rowIndex];
          tableApp.$refs.editModal.show();
        },
        editModalShown: function () {
          // console.log('edit shown');
        },
        editModalHidden: function (index) {
          // console.log('edit hidden');
          $(tableApp.$refs.editModal.$el).removeClass('is-visible');
        },
        editPrimaryClick: function () {
          // Save button clicked. Need to save this.internaldata with api after validation
          if (this.validateAddress(this.selectedData)) {
            // console.log('saving');
            // console.log(this.selectedData);
            this.$refs.editModal.hide();
            this.save();
          }
        },
        editSecondaryClick: function () {
          // Cancel button clicked. Need to update filteredData with tempData stored from when the modal openend.
          // console.log('edit secondary');
          for (property in this.filteredData[this.tempIndex]) {
            this.filteredData[this.tempIndex][property] = this.tempData[property];
          }
          this.$refs.editModal.hide();
        },
        editDeleteAddress: function (selectedId) {
          // Delete button clicked. Need to delete this index from internalData.
          // console.log('delete clicked');
          var confirmDelete = confirm("Are you sure you want to delete this address?");
          if (confirmDelete == true) {
            this.internalData = this.internalData.filter(function (obj) {
              return obj.id !== selectedId;
            });
            //this.$refs.editModal.hide();
            this.save();
          }
        },
        save: function () {
          // Save should throw up loading screen, make ajax call, then remove loading screen on complete
          // console.log('Save Internal Data!');
          tableApp.showLoader();
          var address_json = JSON.stringify(this.internalData);
          $.ajax({
            type: 'POST',
            url: addresses_ajax_obj.ajaxurl, // or ajax_obj.ajaxurl if using on frontend
            data: {
              'action': 'addresses',
              'security': addresses_ajax_obj.addresses_ajax_nonce,
              'address_data': address_json
            },
            success: function (data) {
              // console.log(data);
              tableApp.hideLoader();
            },
            error: function (errorThrown) {
              // console.log(errorThrown);
            },
            complete: function () {
              // console.log('complete');
            }
          });
        }
      },
      props: {
        "rowSize": "",
        "autoWidth": { type: Boolean, default: true },
        "sortable": { type: Boolean, default: true },
        "title": { type: String, default: "My Addresses" },
        "actionBarAriaLabel": "Custom action bar aria label",
        "batchCancelLabel": "Cancel",
        "zebra": { type: Boolean, default: false },
        "use_actions": { type: Boolean, default: true },
        "use_batchActions": { type: Boolean, default: false },
        "helperText": { type: String, default: "Manage your address book to quickly access addresses upon checkout." },
        "editDeleteKind": { type: String, default: "danger" },
        "loaderOverlay": { type: Boolean, default: true },
        "clearOnReselect": { type: Boolean, default: true },
        "removable": { type: Boolean, default: true },
        "multiple": { type: Boolean, default: false },
        "dropTargetLabel": { type: String, default: 'Click here or drag and drop to upload your CSV' }
      }
    });
  }
  if (document.getElementById('combo-app')) {
    // Address shipping field listeners
    save_address_listeners();
    // Remove field from address plugin
    // jQuery('#thwma-shipping-alt_field').remove();
    jQuery('#thwma-shipping-alt_field label').append(' (Legacy)');
    // Clear values for shipping fields
    $('#shipping_first_name').val('');
    $('#shipping_last_name').val('');
    $('#shipping_company').val('');
    $('#shipping_address_1').val('');
    $('#shipping_address_2').val('');
    $('#shipping_city').val('');
    // $('#shipping_country').val('');
    // $('#shipping_country').trigger('change.select2');
    // $('#shipping_country').trigger('change');
    // $('#shipping_country').trigger('click');
    $('#shipping_state').val('');
    $('#shipping_state').trigger('change.select2');
    $('#shipping_postcode').val('');
    $('#shipping_phone').val('');
    // Add new copy with link to management tool.
    jQuery('#ship-to-different-address').append('Manage addresses with the <a href="/profile/addresses?src=checkout">Address Management Tool</a>.');
    // Add new link that saves current address to address book.
    jQuery('#shipping_country_field').append('<br /><a id="save_address" href="#" title="Save this address to your address management tool" onclick="save_address_checkout(event)" style="display: inline-block; padding: 5px 0;">Save this Address</a><img id="save_address_loader" style="display: none; width: 18px; margin-left: 5px; position: relative; top: 5px;" src="' + stylesheet_directory_uri + '/app/assets/img/loader.gif" alt="Loading"/>');
    comboApp = new Vue({
      el: '#combo-app',
      template: '#combo-template',
      data: function () {
        // console.log(internalDataCombo);
        return {
          value: '',
          highlight: '',
        };
      },
      computed: {
      },
      methods: {
        onChange: function (value) {
          // The returned value is the id property which is the index + 1;
          var selectedAddress = internalData.filter(function (object) {
            return object.id == value;
          });
          selectedAddress = selectedAddress[0];
          // Populate woocommerce fields with new values
          $('#shipping_first_name').val(selectedAddress.first_name);
          $('#shipping_last_name').val(selectedAddress.last_name);
          $('#shipping_company').val(selectedAddress.company);
          $('#shipping_address_1').val(selectedAddress.street_address_1);
          $('#shipping_address_2').val(selectedAddress.street_address_2);
          $('#shipping_city').val(selectedAddress.city);
          if (selectedAddress.country == null || selectedAddress.country == '') {
            $('#shipping_country').val('US');
          } else {
            $('#shipping_country').val(selectedAddress.country);
          }
          $('#shipping_country').trigger('change.select2');
          $('#shipping_country').trigger('change');
          $('#shipping_country').trigger('click');
          $('#shipping_state').val(selectedAddress.state);
          $('#shipping_state').trigger('change.select2');
          $('#shipping_postcode').val(selectedAddress.postal_code);
          $('#shipping_phone').val(selectedAddress.phone);
        },
        onFilter: function (filter) {
          // console.log(filter);
          var pat = new RegExp(filter, 'ui');
          if (this.userFilter) {
            // console.log(filter);
            this.options = internalDataCombo;
            // console.log(this.options);
          }
          if (this.userHighlight && this.options.length > 0) {
            var found = this.options.find(function (opt) {
              pat.test(opt.label)
            });
            if (found) {
              this.highlight = found.value;
            } else {
              this.highlight = '';
            }
          }
        },
      },
      props: {
        "label": { type: String, default: "Search for an Address" },
        "initialValue": { type: String, default: "" },
        "helperText": { type: String, default: "Helper Text" },
        "invalidMessage": { type: String, default: "Invalid Message" },
        "title": { type: String, default: "" },
        "disabled": { type: Boolean, default: false },
        "autoFilter": { type: Boolean, default: true },
        "autoHighlight": { type: Boolean, default: true },
        "userHighlight": { type: Boolean, default: true },
        "userFilter": { type: Boolean, default: true },
        "use_helperTextSlot": { type: Boolean, default: true },
        "use_invalidMessageSlot": { type: Boolean, default: true },
        "options": { type: Array, default: function () { return internalDataCombo; } }
        //"selectionFeedback": {type: String, default: "top-after-reopen"}
      }
    });
  }


});