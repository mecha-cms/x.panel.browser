<?php

$function_browser = <<<JS
function browser(width, height) {
    let left = (screen.width / 2) - (width / 2),
        top = (screen.height / 2) - (height / 2);
    return window.open(this.href, 'browser', 'width=' + width + ',height=' + height + ',top=' + top + ',left=' + left);
}
JS;

$function_insert = <<<JS
function insert(name, key) {
    let top = window.opener || window.parent || window.top,
        forms = top.document.forms,
        f = forms[name] && forms[name][key],
        str = this.href;
    if (f) {
        // Focus to the parent window after insert
        top.focus();
        // Insert `str` as a value
        if ('value' in f) {
            // Check if source code editor available
            let editor = top.TE && top.TE.instances[f.id || key];
            if (editor) {
                editor.trim('\\n\\n', "", "", "").insert(str + '\\n\\n', -1, true);
            } else {
                if ('textarea' === f.nodeName.toLowerCase()) {
                    f.value += '\\n\\n' + str + '\\n\\n';
                    // Put caret at the end of the value
                    f.selectionStart = f.selectionEnd = f.value.length;
                    // Focus to the field
                    f.focus();
                } else {
                    f.value = str;
                    // Focus and select the field value
                    f.focus();
                    f.select();
                }
            }
        // Insert `str` as a content
        } else if ('innerHTML' in f) {
            f.innerHTML = str;
        } else {
            alert(str);
        }
    }
    // Close the current window after insert
    window.close();
}
JS;

// Check if we are in the asset(s) page
if (isset($_['chops'][0]) && 'asset' === $_['chops'][0]) {
    // Load the insert function only in the asset(s) page
    Asset::script($function_insert, 10);
    if (Get::get('window')) {
        Hook::set('_', function($_) {
            // Hide navigation bar if we are in browser mode
            $_['lot']['bar']['hidden'] = true;
            // Create insert task on every file in the list
            $key = Get::get('key');
            $name = Get::get('name');
            if (isset($_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['files']['lot']['files']['lot'])) {
                foreach ($_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['files']['lot']['files']['lot'] as $k => &$v) {
                    // Hide edit task
                    $v['tasks']['g']['hidden'] = true;
                    // Hide delete task
                    $v['tasks']['l']['hidden'] = true;
                    // Create insert task
                    $v['tasks']['insert'] = [
                        'active' => $f = is_file($k),
                        'icon' => 'M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z',
                        'title' => 'Insert',
                        '2' => $f ? ['onclick' => 'return insert.call(this, \'' . $name . '\', \'' . $key . '\'), false;'] : [],
                        'description' => 'Insert this file to the editor.',
                        'link' => $f ? To::URL($k) : null,
                        'stack' => 10
                    ];
                }
            }
            return $_;
        }, 10);
    }
}

// Check if we are in the page editor
if (isset($_['layout']) && ('page' === $_['layout'] || 0 === strpos($_['layout'], 'page.'))) {
    // Load the browser function only in the page editor
    Asset::script($function_browser, 10);
    Hook::set('_', function($_) use($url) {
        // Add a browser link on the page `content` field
        if (isset($_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['page']['lot']['fields']['lot']['content'])) {
            $_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['page']['lot']['fields']['lot']['content']['description'] = 'This is an example description for the page content field. <a href="' . $url . $_['/'] . '/::g::/asset/1?key=page[content]&name=set&window=1" onclick="return browser.call(this, 600, 300), false;" target="_blank">Click here</a> to insert a file.';
        }
        // Add a browser link on the page `link` field
        if (isset($_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['data']['lot']['fields']['lot']['link'])) {
            $_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['data']['lot']['fields']['lot']['link']['description'] = 'This is an example description for the page link field. <a href="' . $url . $_['/'] . '/::g::/asset/1?key=page[link]&name=set&window=1" onclick="return browser.call(this, 600, 300), false;" target="_blank">Click here</a> to insert a file.';
        }
        return $_;
    }, 10);
}
