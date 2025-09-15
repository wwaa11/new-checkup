import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

// Make jQuery available globally
import $ from "jquery";
window.$ = window.jQuery = $;
