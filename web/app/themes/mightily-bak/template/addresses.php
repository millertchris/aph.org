<?php

// Template name: Addresses
get_header();

$current_user = wp_get_current_user();

?>

<div class="interior-page">
    <section class="layout catch-all">
        <div class="wrapper carbon-app addresses-app">
            <?php if(isset($_GET['src']) && $_GET['src'] == 'checkout') : ?>
              <a class="btn back-to-checkout" href="/checkout"><i class="fa fa-chevron-left" aria-hidden="true"></i> Back to Checkout</a>
            <?php endif; ?>
            <div class="row">
                <div class="col">
                    <div id="table-app"></div>
                </div>
            </div>
        </div>
    </section>
</div>
<script type="text/x-template" id="addresses-template">
    <div>  
    <cv-text-input
      label="You are?"
      v-model="yourName"
      placeholder="Tell me your name then click below, or I'll call you Bob"
    ></cv-text-input>
    <p>{{ hello }}</p>

    <cv-content-switcher>
      <cv-content-switcher-button owner-id="csb-1" :selected="isSelected(0)"
        >Tiles</cv-content-switcher-button
      >
      <cv-content-switcher-button owner-id="csb-2" :selected="isSelected(1)"
        >Buttons</cv-content-switcher-button
      >
      <cv-content-switcher-button owner-id="csb-3" :selected="isSelected(2)"
        >Other</cv-content-switcher-button
      >
    </cv-content-switcher>

    <section style="margin: 10px 0;">
      <cv-content-switcher-content owner-id="csb-1" class="flexme">
        <cv-tile>
          <h1>Hello</h1>
          <p>This is some tile content</p>
        </cv-tile>
        <cv-tile kind="expandable">
          <h1>Hello</h1>
          <p>This is some tile content</p>
          <template slot="below">
            <h2>More</h2>
            <ul>
              <li>This</li>
              <li>is some</li>
              <li>more</li>
              <li>content</li>
            </ul>
          </template>
        </cv-tile>
        <cv-tile kind="selectable" value="tile-1">
          <h1>Hello</h1>
          <p>This is some tile content</p>
          <template slot="below">
            <h2>More</h2>
            <ul>
              <li>This</li>
              <li>is some</li>
              <li>more</li>
              <li>content</li>
            </ul>
          </template>
        </cv-tile>
      </cv-content-switcher-content>
      <cv-content-switcher-content owner-id="csb-2">
        <cv-button>Default/Primary</cv-button>
        <cv-button kind="secondary">Secondary</cv-button>
        <cv-button kind="tertiary">Tertiary</cv-button>
        <cv-button kind="ghost">Ghost</cv-button>
        <cv-button kind="danger">Danger</cv-button>
        <cv-button kind="danger--primary">Danger-Primary</cv-button>
      </cv-content-switcher-content>
      <cv-content-switcher-content owner-id="csb-2">
        <cv-checkbox label="Checkbox 1" value="value-1"></cv-checkbox>
        <cv-checkbox label="Checkbox 2" value="value-2"></cv-checkbox>

        <cv-radio-group>
          <cv-radio-button name="test" value="test-1" label="test-1" checked />
          <cv-radio-button name="test" value="test-2" label="test-2" />
          <cv-radio-button name="test" value="test-3" label="test-3" />
        </cv-radio-group>
      </cv-content-switcher-content>
      <cv-content-switcher-content owner-id="csb-3">
        <cv-dropdown value="value">
          <cv-dropdown-item value="10">Option with value 10</cv-dropdown-item>
          <cv-dropdown-item value="20">Option with value 20</cv-dropdown-item>
          <cv-dropdown-item value="30">Option with value 30</cv-dropdown-item>
          <cv-dropdown-item value="40">Option with value 40</cv-dropdown-item>
          <cv-dropdown-item value="50">Option with value 50</cv-dropdown-item>
        </cv-dropdown>
        
        <br>
        <cv-progress :initialStep="2" :steps="['Start', 'Speed up', 'Travel', 'Slow down', 'Stop']" />
        <br>

        <cv-pagination :number-of-items="1250" />
      </cv-content-switcher-content>
    </section>
  </div>
</script>
<script type="text/x-template" id="button-template">
    <cv-accordion @change="actionChange" ref="acc">
    <cv-accordion-item :open="open[0]">
      <template slot="title">Section 1 title </template>
      <template slot="content">
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
      </template>
    </cv-accordion-item>
    <cv-accordion-item :open="open[1]">
      <template slot="title">Section 2 title</template>
      <template slot="content">
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
      </template>
    </cv-accordion-item>
    <cv-accordion-item :open="open[2]">
      <template slot="title">Section 3 title</template>
      <template slot="content">
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
      </template>
    </cv-accordion-item>
    <cv-accordion-item :open="open[3]">
      <template slot="title">Section 4 title</template>
      <template slot="content">
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
      </template>
    </cv-accordion-item>
  </cv-accordion>  
</script>

<script type="text/x-template" id="table-template">
    <div>
  <cv-loading ref="loader" :active="loaderActive" :overlay="loaderOverlay"></cv-loading>
  <cv-data-table
  :row-size="rowSize"
  :auto-width="autoWidth"
  :sortable="sortable"
  :title="title"
  :action-bar-aria-label="actionBarAriaLabel"
  :batch-cancel-label="batchCancelLabel"
  :zebra="zebra"
  :columns="columns"
  @search="onFilter"
  :pagination="pagination" @pagination="actionOnPagination"
  v-model="rowSelects" @row-select-change="actionRowSelectChange"
  @sort="onSort"
  
  :helper-text="helperText" @overflow-menu-click="onOverflowMenuClick"  ref="table">
  <template slot="data">
    <cv-data-table-row
      v-for="(row, rowIndex) in filteredData"
    >
      <cv-data-table-cell>{{row.group}}</cv-data-table-cell>
      <cv-data-table-cell>{{row.fq_account}}</cv-data-table-cell>
      <cv-data-table-cell>{{row.last_name}}, {{row.first_name}}</cv-data-table-cell>
      <!-- <cv-data-table-cell>{{row.first_name}}</cv-data-table-cell>
      <cv-data-table-cell>{{row.last_name}}</cv-data-table-cell> -->
      <cv-data-table-cell>{{row.company}}</cv-data-table-cell>
      <cv-data-table-cell class="cell-address">
        <div class="address-wrap">
            <div class="address-data">
              <div class="street_address_1">
                {{row.street_address_1}}
              </div>
              <div v-if="row.street_address_2 != ''" class="street_address_2">
                {{row.street_address_2}}
              </div>
              <div class="city-state">
                {{row.city}}, {{row.state}}
              </div>
              <div class="postal-country">
                {{row.postal_code}} {{row.country}}
              </div>
              <div class="phone">
                {{row.phone}}
              </div>              
            </div>
            <div class="address-controls">
              <a href="#" @click.prevent="showEditModal(rowIndex)">Edit</a><br />
              <a href="#" @click.prevent="editDeleteAddress(row.id)">Delete</a>
            </div>
        </div>
      </cv-data-table-cell>
      <!-- <cv-data-table-cell>
        <cv-button type="button" @click="showEditModal(rowIndex)">
          Edit
          <svg class="bx--btn__icon" focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" aria-hidden="true" style="will-change: transform;"><rect width="28" height="2" x="2" y="26"></rect><path d="M25.4,9c0.8-0.8,0.8-2,0-2.8c0,0,0,0,0,0l-3.6-3.6c-0.8-0.8-2-0.8-2.8,0c0,0,0,0,0,0l-15,15V24h6.4L25.4,9z M20.4,4L24,7.6	l-3,3L17.4,7L20.4,4z M6,22v-3.6l10-10l3.6,3.6l-10,10H6z"></path><title>Edit</title></svg>
        </cv-button>
      </cv-data-table-cell> -->

      <!-- <template slot="expandedContent"
        >A variety of content types can live here. Be sure to follow Carbon design guidelines for spacing and
        alignment.</template> -->
    </cv-data-table-row>
  </template>
  <template v-if="use_actions" slot="actions">
    <!-- <cv-data-table-action @click="action1"></cv-data-table-action> -->
    <!-- <cv-data-table-action @click="action2">
      <svg focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" aria-hidden="true" style="will-change: transform;"><polygon points="6 17 7.41 18.41 15 10.83 15 30 17 30 17 10.83 24.59 18.41 26 17 16 7 6 17"></polygon><path d="M6,8V4H26V8h2V4a2,2,0,0,0-2-2H6A2,2,0,0,0,4,4V8Z"></path><title>Upload</title></svg>
    </cv-data-table-action>
    <cv-data-table-action @click="action2">
      <svg fill-rule="evenodd" height="16" name="edit" role="img" viewBox="0 0 16 16" width="16" aria-label="Edit" alt="Edit">
        <title>Edit</title>
        <path d="M7.926 3.38L1.002 9.72V12h2.304l6.926-6.316L7.926 3.38zm.738-.675l2.308 2.304 1.451-1.324-2.308-2.309-1.451 1.329zM.002 9.28L9.439.639a1 1 0 0 1 1.383.03l2.309 2.309a1 1 0 0 1-.034 1.446L3.694 13H.002V9.28zM0 16.013v-1h16v1z"></path>
      </svg>
    </cv-data-table-action>
    <cv-data-table-action @click="action3">
      <svg fill-rule="evenodd" height="16" name="settings" role="img" viewBox="0 0 15 16" width="15" aria-label="Settings" alt="Settings">
        <title>Settings</title>
        <path d="M7.53 10.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5zm0 1a3.5 3.5 0 1 1 0-7 3.5 3.5 0 0 1 0 7z"></path>
        <path d="M6.268 2.636l-.313.093c-.662.198-1.28.52-1.822.946l-.255.2-1.427-.754-1.214 1.735 1.186 1.073-.104.31a5.493 5.493 0 0 0-.198 2.759l.05.274L1 10.33l1.214 1.734 1.06-.56.262.275a5.5 5.5 0 0 0 2.42 1.491l.312.093L6.472 15H8.59l.204-1.636.313-.093a5.494 5.494 0 0 0 2.21-1.28l.26-.248 1.09.576 1.214-1.734-1.08-.977.071-.29a5.514 5.514 0 0 0-.073-2.905l-.091-.302 1.15-1.041-1.214-1.734-1.3.687-.257-.22a5.487 5.487 0 0 0-1.98-1.074l-.313-.093L8.59 1H6.472l-.204 1.636zM5.48.876A1 1 0 0 1 6.472 0H8.59a1 1 0 0 1 .992.876l.124.997a6.486 6.486 0 0 1 1.761.954l.71-.375a1 1 0 0 1 1.286.31l1.215 1.734a1 1 0 0 1-.149 1.316l-.688.622a6.514 6.514 0 0 1 .067 2.828l.644.581a1 1 0 0 1 .148 1.316l-1.214 1.734a1 1 0 0 1-1.287.31l-.464-.245c-.6.508-1.286.905-2.029 1.169l-.124.997A1 1 0 0 1 8.59 16H6.472a1 1 0 0 1-.992-.876l-.125-.997a6.499 6.499 0 0 1-2.274-1.389l-.399.211a1 1 0 0 1-1.287-.31L.181 10.904A1 1 0 0 1 .329 9.59l.764-.69a6.553 6.553 0 0 1 .18-2.662l-.707-.64a1 1 0 0 1-.148-1.315l1.214-1.734a1 1 0 0 1 1.287-.31l.86.454a6.482 6.482 0 0 1 1.576-.819L5.48.876z"></path>
      </svg>
    </cv-data-table-action> -->
    <cv-button small data-modal-target="#modal-address-help">
      Help <svg class="bx--btn__icon" focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" aria-hidden="true" style="will-change: transform;"><path d="M16,2A14,14,0,1,0,30,16,14,14,0,0,0,16,2Zm0,26A12,12,0,1,1,28,16,12,12,0,0,1,16,28Z"></path><circle cx="16" cy="23.5" r="1.5"></circle><path d="M17,8H15.5A4.49,4.49,0,0,0,11,12.5V13h2v-.5A2.5,2.5,0,0,1,15.5,10H17a2.5,2.5,0,0,1,0,5H15v4.5h2V17a4.5,4.5,0,0,0,0-9Z"></path><title>Help</title></svg>
    </cv-button>
    <cv-button small data-modal-target="#modal-address-new">
      Add new <svg class="bx--btn__icon" focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" aria-hidden="true" style="will-change: transform;"><polygon points="17,15 17,8 15,8 15,15 8,15 8,17 15,17 15,24 17,24 17,17 24,17 24,15"></polygon><title>Add</title></svg>
    </cv-button>
    <cv-button small @click="actionDownload">
      Download CSV <svg class="bx--btn__icon" focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" aria-hidden="true" style="will-change: transform;"><polygon points="26 15 24.59 13.59 17 21.17 17 2 15 2 15 21.17 7.41 13.59 6 15 16 25 26 15"></polygon><path d="M26,24v4H6V24H4v4H4a2,2,0,0,0,2,2H26a2,2,0,0,0,2-2h0V24Z"></path><title>Download</title></svg>
    </cv-button>
    <cv-button small data-modal-target="#modal-address-upload">
      Upload CSV <svg class="bx--btn__icon" focusable="false" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" aria-hidden="true" style="will-change: transform;"><polygon points="6 17 7.41 18.41 15 10.83 15 30 17 30 17 10.83 24.59 18.41 26 17 16 7 6 17"></polygon><path d="M6,8V4H26V8h2V4a2,2,0,0,0-2-2H6A2,2,0,0,0,4,4V8Z"></path><title>Upload</title></svg>
    </cv-button>
  </template>
  <!-- <template v-if="use_batchActions" slot="batch-actions">
    <cv-button @click="onBatchAction1">Delete</cv-button>
    <cv-button @click="onBatchAction2">Save</cv-button>
    <cv-button @click="onBatchAction3">Download</cv-button>
  </template> --> 
</cv-data-table>
<cv-modal ref="helpModal" id="modal-address-help" @modal-shown="helpModalShown" @modal-hidden="helpModalHidden">
  <template slot="title">Need Assistance?</template>
  <template slot="content">
    <p>
      There will be some instructions here for adding and removing addresses, and how to access them upon checkout.
    </p>
  </template>
</cv-modal>
<cv-modal ref="newModal" id="modal-address-new" @modal-shown="newModalShown" @modal-hidden="newModalHidden" @primary-click="newPrimaryClick" @secondary-click="newSecondaryClick">
  <template slot="title">Add an Address</template>
  <template slot="content">
    <p v-if="errors.length">
      <b>Please correct the following error(s):</b>
      <ul>
        <li v-for="error in errors">{{ error }}</li>
      </ul>
    </p>    
    <p>Group Name: <input type="text" v-model="newData.group"/></p>
    <p>FQ Account: <input type="text" v-model="newData.fq_account"/></p>
    <p>First Name: <input type="text" v-model="newData.first_name"/></p>
    <p>Last Name: <input type="text" v-model="newData.last_name"/></p>
    <p>Company: <input type="text" v-model="newData.company"/></p>
    <p>Street Address 1: <input type="text" v-model="newData.street_address_1" placeholder="Do not use P.O. Box for international shipping"/></p>
    <p>Street Address 2: <input type="text" v-model="newData.street_address_2"/></p>
    <p>City: <input type="text" v-model="newData.city"/></p>
    <!-- <p>State: <input type="text" v-model="newData.state"/></p> -->
    <p>State:
      <span v-if="newData.country == 'US'">
        <select v-model="newData.state">
          <option v-for="(item, index) in us_states" v-bind:value="index" :selected="newData.state">{{item}}</option>
        </select>
      </span>
      <span v-if="newData.country == 'CA'">
        <select v-model="newData.state">
          <option v-for="(item, index) in ca_states" v-bind:value="index" :selected="newData.state">{{item}}</option>
        </select>
      </span>
      <span v-if="newData.country != 'US' && newData.country != 'CA'">
        <input type="text" v-model="newData.state"/>     
      </span>
    </p>
    <p>Zip: <input type="text" v-model="newData.postal_code"/></p>
    <p>Country:
      <select v-model="newData.country">
        <option v-for="(item, index) in countries" v-bind:value="index" :selected="newData.country">{{item}}</option>
      </select>
    </p>
    <p>Phone: <input type="text" v-model="newData.phone"/></p>
  </template>
  <template slot="secondary-button">Cancel</template>
  <template slot="primary-button">Save</template>
</cv-modal>
<cv-modal ref="uploadModal" id="modal-address-upload" @modal-shown="uploadModalShown" @modal-hidden="uploadModalHidden" @primary-click="uploadPrimaryClick" @secondary-click="uploadSecondaryClick">
  <template slot="title">Upload CSV</template>
  <template slot="content">
    <p>Uploading a CSV will overwrite any addresses previously stored in your address book.</p>
    <input id="uploadFileInput" type="file" @change="uploadChange" accept=".csv"/>
    <!-- <cv-file-uploader
      ref="uploadFile"
      accept="text/csv"
      :clear-on-reselect="clearOnReselect"
      :removable="removable"
      :multiple="multiple"
      :dropTargetLabel="dropTargetLabel"
      label='Add your CSV file below then click "Import" to import addresses into your address book.'
      @input="uploadInput"
      @change="uploadChange"
    >
    </cv-file-uploader> -->
  </template>
  <template slot="secondary-button">Cancel</template>
  <template slot="primary-button">Import</template>
</cv-modal>
<cv-modal ref="editModal" id="modal-address-edit" @modal-shown="editModalShown" @modal-hidden="editModalHidden" @primary-click="editPrimaryClick" @secondary-click="editSecondaryClick">
  <template slot="title">Edit Address</template>
  <template slot="content">
    <p v-if="errors.length">
      <b>Please correct the following error(s):</b>
      <ul>
        <li v-for="error in errors">{{ error }}</li>
      </ul>
    </p>    
    <p>Group Name: <input type="text" v-model="selectedData.group"/></p>
    <p>FQ Account: <input type="text" v-model="selectedData.fq_account"/></p>
    <p>First Name: <input type="text" v-model="selectedData.first_name"/></p>
    <p>Last Name: <input type="text" v-model="selectedData.last_name"/></p>
    <p>Company: <input type="text" v-model="selectedData.company"/></p>
    <p>Street Address 1: <input type="text" v-model="selectedData.street_address_1" placeholder="Do not use P.O. Box for international shipping"/></p>
    <p>Street Address 2: <input type="text" v-model="selectedData.street_address_2"/></p>
    <p>City: <input type="text" v-model="selectedData.city"/></p>
    <p>State:
      <span v-if="selectedData.country == 'US'">
        <select v-model="selectedData.state">
          <option v-for="(item, index) in us_states" v-bind:value="index" :selected="selectedData.state">{{item}}</option>
        </select>
      </span>
      <span v-if="selectedData.country == 'CA'">
        <select v-model="selectedData.state">
          <option v-for="(item, index) in ca_states" v-bind:value="index" :selected="selectedData.state">{{item}}</option>
        </select>
      </span>
      <span v-if="selectedData.country != 'US' && selectedData.country != 'CA'">
        <input type="text" v-model="selectedData.state"/>     
      </span>      
    </p>
    <p>Zip: <input type="text" v-model="selectedData.postal_code"/></p>
    <p>Country:
      <select v-model="selectedData.country">
        <option v-for="(item, index) in countries" v-bind:value="index" :selected="selectedData.country">{{item}}</option>
      </select>
    </p>
    <p>Phone: <input type="text" v-model="selectedData.phone"/></p>
    <!-- <cv-button type="button" :kind="editDeleteKind" @click="editDeleteAddress(selectedData.id)">Delete</cv-button> -->
  </template>
  <template slot="secondary-button">Cancel</template>
  <template slot="primary-button">Save</template>
</cv-modal>
</div>
</script>
<?php get_footer(); ?>