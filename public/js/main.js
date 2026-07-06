/*  main.js - Aletho Bibliotheek frontend orchestrator. */
import { Popins } from './modules/popins.js';
import { TagInput } from './modules/taginput.js';
import { Utility } from './modules/utility.js';
import { Suggestions } from './modules/suggestions.js';
import { AppState } from './appstate.js'
import { Events } from "./events.js";

$(function() {
    const alertShowClass        = 'aletho-global-show';
    const fieldEditableClass    = 'field-editable';
    const fieldChangedClass     = 'field-changed';
    const $alert                = $('.aletho-global-success, .aletho-global-failure');

    const tagInputConfigs       = [
        Utility.makeTagConfig('schrijver'),
        Utility.makeTagConfig('genre'),
        Utility.makePopTagConfig('schrijver'),
        Utility.makePopTagConfig('genre'),
    ];
    tagInputConfigs.forEach( config => TagInput.init(config) );

    $('#status-type').trigger('change');

    Events.init();

    if ($alert.length) {
        setTimeout(() => { $alert.addClass(alertShowClass); }, 100);
        setTimeout(() => { $alert.removeClass(alertShowClass); }, 3500);
        setTimeout(() => { $alert.remove(); }, 4000);
    }
});

/*  Legacy Debug functions for XHR requests
    $(document).ajaxSuccess(function (event, xhr, settings, data) {
        console.log('AJAX SUCCESS:', settings.url, data);
    });

    $(document).ajaxError(function (event, xhr, settings, error) {
        console.error('AJAX ERROR:', settings.url, xhr.responseText);
    });
*/