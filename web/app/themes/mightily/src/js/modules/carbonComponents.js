// Vue.use(window['carbon-vue'].default);

// var internalData =  [
//     [
//       "Teacher",
//       "Jane",
//       "Graham",
//       "APH",
//       "123 Any St",
//       "Louisville",
//       "KY",
//       "40202"
//     ],
//     [
//       "Accounts",
//       "John",
//       "Simmons",
//       "APH",
//       "456 There St",
//       "Louisville",
//       "KY",
//       "40202"
//     ],
//     [
//       "Teacher",
//       "Kylie",
//       "Martin",
//       "Skeen Elementary",
//       "789 Where St",
//       "Louisville",
//       "KY",
//       "40202"
//     ],
//     [
//       "Teacher",
//       "Luke",
//       "Deebs",
//       "Louis High",
//       "987 Could Wood Dr",
//       "Louisville",
//       "KY",
//       "40202"
//     ],
//     [
//       "Accounts",
//       "Norm",
//       "Walters",
//       "APH",
//       "654 Goody Ln",
//       "Louisville",
//       "KY",
//       "40202"      
//     ],
//     [
//       "Teacher",
//       "Jane",
//       "Graham",
//       "APH",
//       "123 Any St",
//       "Louisville",
//       "KY",
//       "40202"
//     ],
//     [
//       "Accounts",
//       "John",
//       "Simmons",
//       "APH",
//       "456 There St",
//       "Louisville",
//       "KY",
//       "40202"
//     ],
//     [
//       "Teacher",
//       "Kylie",
//       "Martin",
//       "Skeen Elementary",
//       "789 Where St",
//       "Louisville",
//       "KY",
//       "40202"
//     ],
//     [
//       "Teacher",
//       "Luke",
//       "Deebs",
//       "Louis High",
//       "987 Could Wood Dr",
//       "Louisville",
//       "KY",
//       "40202"
//     ],
//     [
//       "Teacher",
//       "Jane",
//       "Graham",
//       "APH",
//       "123 Any St",
//       "Louisville",
//       "KY",
//       "40202"
//     ],
//     [
//       "Accounts",
//       "John",
//       "Simmons",
//       "APH",
//       "456 There St",
//       "Louisville",
//       "KY",
//       "40202"
//     ],
//     [
//       "Teacher",
//       "Kylie",
//       "Martin",
//       "Skeen Elementary",
//       "789 Where St",
//       "Louisville",
//       "KY",
//       "40202"
//     ],
//     [
//       "Teacher",
//       "Luke",
//       "Deebs",
//       "Louis High",
//       "987 Could Wood Dr",
//       "Louisville",
//       "KY",
//       "40202"
//     ],
//     [
//       "Teacher",
//       "Jane",
//       "Graham",
//       "APH",
//       "123 Any St",
//       "Louisville",
//       "KY",
//       "40202"
//     ],
//     [
//       "Accounts",
//       "John",
//       "Simmons",
//       "APH",
//       "456 There St",
//       "Louisville",
//       "KY",
//       "40202"
//     ],
//     [
//       "Teacher",
//       "Kylie",
//       "Martin",
//       "Skeen Elementary",
//       "789 Where St",
//       "Louisville",
//       "KY",
//       "40202"
//     ],
//     [
//       "Teacher",
//       "Luke",
//       "Deebs",
//       "Louis High",
//       "987 Could Wood Dr",
//       "Louisville",
//       "KY",
//       "40202"
//     ],
//     [
//       "Teacher",
//       "Jane",
//       "Graham",
//       "APH",
//       "123 Any St",
//       "Louisville",
//       "KY",
//       "40202"
//     ],
//     [
//       "Accounts",
//       "John",
//       "Simmons",
//       "APH",
//       "456 There St",
//       "Louisville",
//       "KY",
//       "40202"
//     ],
//     [
//       "Teacher",
//       "Kylie",
//       "Martin",
//       "Skeen Elementary",
//       "789 Where St",
//       "Louisville",
//       "KY",
//       "40202"
//     ],
//     [
//       "Teacher",
//       "Luke",
//       "Deebs",
//       "Louis High",
//       "987 Could Wood Dr",
//       "Louisville",
//       "KY",
//       "40202"
//     ]
// ];

// var internalDataTable = internalData.map(function(item){
//   return [
//     item[0],
//     item[1],
//     item[2],
//     item[3],
//     item[4]
//   ];
// });

// var internalDataCombo = internalData.map(function(item, index){
//     var name = 'name';
//     var label = '(' + item[0] + ') ' + item[1] + ' ' + item[2] + ', ' + item[3] + ', ' + item[4] + ', ' + item[5] + ', ' + item[6] + ' ' + item[7];
//     var value = 'value';
//     return {
//       name: name,
//       label: label,
//       value: value,
//     };
// });

// if(document.getElementById('addresses-app')){
// var addressApp = new Vue({
// el: '#addresses-app',
// template: '#addresses-template',
// data: function() {
//     return {
//     yourName: '',
//     notify: false,
//     dataSelected: 0
//     };
// },
// computed: {
//     hello: function() {
//     if (this.yourName.length) {
//         return 'Hello' + this.yourName + '!';
//     } else {
//         return 'Hello Bob!';
//     }
//     },
//     isSelected: function() {
//     return function(index){ this.dataSelected === index };
//     } },
// methods: {
//     sayHi: function() {
//     alert('Stay' +  this.yourName);
//     } }
// });
// }
// if(document.getElementById('button-app')){
// var buttonApp = new Vue({
// el: '#button-app',
// template: '#button-template',
// data: function() {
//     return {
//     open: [false, false, false, false],
//     };
// },
// computed: {
// },
// methods: {
//     actionChange: function(){

//     }
// }
// });
// }
// if(document.getElementById('table-app')){
// var tableApp = new Vue({
// el: '#table-app',
// template: '#table-template',
// data: function() {
//     return {
//     internalData: internalDataTable,
//     filterValue: '',
//     rowSelects: [],
//     sortBy: undefined,
//     sampleOverflowMenu: ['Edit'],
//     columns: [
//         "Group Name",
//         "First Name",
//         "Last Name",
//         "Organization",
//         "Address"
//     ]
//     };
// },
// watch: {
//     data: function() {
//     this.internalData = this.data;
//     },
// },
// computed: {
//     filteredData: function() {
//     if (this.filterValue) {
//         var regex = new RegExp(this.filterValue, 'i');
//         return this.internalData.filter(function(item){
//         return item.join('|').search(regex) >= 0;
//         });
//     } else {
//         return this.internalData;
//     }
//     },
// },
// methods: {
//     onFilter: function(val) {
//     this.filterValue = val;
//     },
//     onSort: function(sortBy) {
//     if (sortBy) {
//         this.internalData.sort(function(a, b){
//         var itemA = a[sortBy.index];
//         var itemB = b[sortBy.index];

//         if (sortBy.order === 'descending') {
//             if (sortBy.index === 2) {
//             // sort as number
//             return parseFloat(itemA) - parseFloat(itemB);
//             } else {
//             return itemB.localeCompare(itemA);
//             }
//         }
//         if (sortBy.order === 'ascending') {
//             if (sortBy.index === 2) {
//             // sort as number
//             return parseFloat(itemB) - parseFloat(itemA);
//             } else {
//             return itemA.localeCompare(itemB);
//             }
//         }
//         return 0;
//         });
//     }
//     },
//     batchAction1: function() {
//     console.log('batch action 1')
//     },
//     onBatchAction1: function() {
//     //this.batchAction1(`selected items: [${this.$refs.table.selectedRows}]`);
//     this.rowSelects = [];
//     },
//     batchAction2: function() { console.log('batch action 2')},
//     onBatchAction2: function() {
//     //this.batchAction2(`selected items: [${this.$refs.table.selectedRows}]`);
//     this.rowSelects = [];
//     },
//     batchAction3: function() { console.log('batch action 3')},
//     onBatchAction3: function() {
//     //this.batchAction3(`selected items: [${this.$refs.table.selectedRows}]`);
//     this.$refs.table.deselect();
//     },
//     action1: function() { console.log('action 1') },
//     action2: function() { console.log('action 2') },
//     action3: function() { console.log('action 3') },
//     actionNew: function() { console.log('add new') },
//     actionOnPagination: function() { console.log('pagination change') },
//     onOverflowMenuClick: function() { console.log('overflow menu click') },
//     actionRowSelectChange: function() { console.log('row selected') },
// },
// props: {
//     "rowSize": "",
//     "autoWidth": false,
//     "sortable": {type: Boolean, default: true},
//     "title": "Table title",
//     "actionBarAriaLabel": "Custom action bar aria label",
//     "batchCancelLabel": "Cancel",
//     "zebra": {type: Boolean, default: false},
//     "use_actions": {type: Boolean, default: true},
//     "use_batchActions": {type: Boolean, default: false},
//     "helperText": "This is some helpful text"
// }
// });
// }
// if(document.getElementById('combo-app')){
//     // Remove field from address plugin
//     jQuery('#thwma-shipping-alt_field').remove();
//     // Add new copy with link to management tool.
//     jQuery('#ship-to-different-address').append('Manage addresses with the <a href="/profile/addresses">Address Management Tool</a>.');
//     var comboApp = new Vue({
//     el: '#combo-app',
//     template: '#combo-template',
//     data: function() {
//         //console.log(internalDataCombo);
//         return {
//                 value: '',
//                 options: internalDataCombo,
//                 highlight: '',
//             };
//     },
//     computed: {
//     },
//     methods: {
//         onChange: function(){
//         console.log('CV ComboBox - change')
//         },
//         onFilter: function(filter) {
//         //console.log(filter);
//         var pat = new RegExp(filter, 'ui');
//         if (this.userFilter) {
//             //console.log(filter);
//             this.options = internalDataCombo;
//             //console.log(this.options);
//         }
//         if (this.userHighlight && this.options.length > 0) {
//             var found = this.options.find(function(opt){
//                 pat.test(opt.label)
//             });
//             if (found) {
//             this.highlight = found.value;
//             } else {
//             this.highlight = '';
//             }
//         }
//         },
//     },
//     props: {
//         "label": {type: String, default: "Search for an Address"},
//         "initialValue": {type: String, default: ""},
//         "helperText": {type: String, default: "Helper Text"},
//         "invalidMessage": {type: String, default: "Invalid Message"},
//         "title": {type: String, default: ""},
//         "disabled": {type: Boolean, default: false},
//         "autoFilter": {type: Boolean, default: true},
//         "autoHighlight": {type: Boolean, default: true},
//         "userHighlight": {type: Boolean, default: true},
//         "userFilter": {type: Boolean, default: true},
//         "use_helperTextSlot": {type: Boolean, default: true},
//         "use_invalidMessageSlot": {type: Boolean, default: true},
//         //"selectionFeedback": {type: String, default: "top-after-reopen"}
//     }
//     });
// }
  //"sortable": {type: Boolean, default: true},