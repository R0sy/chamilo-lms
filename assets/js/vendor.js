var moment = require("moment");
require("moment/min/locales.min");
global.moment = moment;

require("webpack-jquery-ui");
require("webpack-jquery-ui/css");

// JS is equivalent to the normal "bootstrap" package
// no need to set this to a variable, just require it
require("bootstrap");
require("chosen-js");
require("mediaelement");
require("multiselect-two-sides");
require('@fortawesome/fontawesome-free');

require("qtip2");
require("image-map-resizer");
require("cropper");
require("jquery-ui-timepicker-addon/dist/jquery-ui-timepicker-addon");
require("jquery.scrollbar");
require("blueimp-file-upload");
require("select2");
require("timeago");
require("select2/dist/css/select2.css");
require('bootstrap-select/dist/js/bootstrap-select.js');
require('bootstrap-select/dist/css/bootstrap-select.css');
require('flag-icon-css/css/flag-icon.css');
require("bootstrap-daterangepicker");
require("bootstrap-daterangepicker/daterangepicker.scss");
require("fullcalendar/dist/fullcalendar.js");
require("fullcalendar/dist/fullcalendar.css");
require("fullcalendar/dist/gcal.js");
require("fullcalendar/dist/locale-all.js");

//require("readmore-js");

// doesn't work with webpack added directly in /public/libs folder
/*
require("fullcalendar");
require("pwstrength-bootstrap");

require("js-cookie");
require("jquery-ui-timepicker-addon");
require("ckeditor");
*/
