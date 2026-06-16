(function(api) {
    "use strict";
    const cssRules = {};
    function liveUpdateCSS(id, css){
        let _tmp_id = id.replace('[', '_').replace(']', '_').replace(/_*$/, '');
        _tmp_id = 'lakit_cs__' + _tmp_id;
        const styleSelector = document.getElementById( _tmp_id );
        if(styleSelector){
            styleSelector.innerHTML = css;
        }
        else{
            const tmpDiv = document.createElement("div");
            tmpDiv.innerHTML = "<style id='"+_tmp_id+"'>" + css + "</style>";
            document.getElementsByTagName("head")[0].appendChild(tmpDiv.childNodes[0])
        }
    }

    function renderCssRules(){
        let css = '';
        for (const cssKey in cssRules) {
            if(cssRules[cssKey] !== ''){
                css += cssRules[cssKey];
            }
        }
        liveUpdateCSS('cs_style', css);
    }

    function setCssRule( key, rules, value ){
        if(value === ''){
            cssRules[key] = '';
        }
        else{
            if(rules.length){
                let _css = '';
                rules.forEach( function ( rule ){
                    _css += rule.selector + '{' + rule.property + ':' + value + '}';
                });
                cssRules[key] = _css;
            }
        }
        renderCssRules();
    }

    for (const control_name in lakitCustomizeConfigs) {
        api(control_name, function ( controlSetting ){
            controlSetting.bind(function ( value ){
                setCssRule( control_name, lakitCustomizeConfigs[control_name], value );
            });
        });
    }

})(wp.customize);