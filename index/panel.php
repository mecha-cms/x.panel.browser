<?php

$function_browse = <<<JS
function browse(width, height) {
    return window.open(this.href, 'browser', 'height=' + height + ',popup=1,width=' + width);
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
            window.alert(str);
        }
    }
    // Close the current window after insert
    window.close();
}
JS;

// Check if we are in the asset(s) page
if (0 === strpos($_['path'] . '/', 'asset/')) {
    if (!empty($_GET['browser'])) {
        Hook::set('_', function ($_) use ($function_insert) {
            // Load the insert function only in the asset(s) page
            $_['asset'][] = [
                'link' => 'data:text/js;base64,' . To::base64($function_insert),
                'stack' => 10
            ];
            // Hide navigation bar if we are in browse mode
            $_['lot']['bar']['skip'] = true;
            // Create insert task on every file in the list
            $key = $_GET['key'] ?? null;
            $name = $_GET['name'] ?? null;
            if (isset($_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['files']['lot']['files']['lot'])) {
                foreach ($_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['files']['lot']['files']['lot'] as $k => &$v) {
                    $is_file = is_file($k);
                    // Hide edit task
                    $v['tasks']['get']['skip'] = true;
                    // Hide delete task
                    $v['tasks']['let']['skip'] = true;
                    // Create insert task
                    $v['tasks']['insert'] = [
                        '2' => $is_file ? ['onclick' => 'return insert.call(this, \'' . $name . '\', \'' . $key . '\'), false;'] : [],
                        'active' => $is_file,
                        'description' => 'Insert file URL.',
                        'icon' => 'M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z',
                        'link' => $is_file ? To::URL($k) : null,
                        'stack' => 10,
                        'title' => 'Insert'
                    ];
                }
                unset($v);
            }
            return $_;
        }, 20);
    }
}

// Check if we are in the page editor
$is_page = 0 === strpos($_['type'] . '/', 'page/');
if (!$_['type'] && ($file = $_['file'])) {
    $is_page = false !== strpos(',archive,draft,page,', ',' . pathinfo($file, PATHINFO_EXTENSION) . ',');
}

if ($is_page) {
    Hook::set('_', function ($_) use ($function_browse) {
        // Load the browse function only in the page editor
        $_['asset'][] = [
            'link' => 'data:text/js;base64,' . To::base64($function_browse),
            'stack' => 10
        ];
        // Add a browse link on the `page[content]` field
        if (isset($_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['page']['lot']['fields']['lot']['content'])) {
            $_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['page']['lot']['fields']['lot']['content']['description'] = 'This is an example description for the page content field. <a href="' . x\panel\to\link([
                'hash' => null,
                'part' => 1,
                'path' => 'asset',
                'query' => [
                    'browser' => 1,
                    'key' => 'page[content]',
                    'name' => 'set',
                    'type' => null
                ],
                'task' => 'get'
            ]) . '" onclick="return browse.call(this, 600, 300), false;" rel="opener" target="_blank">Click here</a> to insert a file.';
        }
        // Add a browse link on the `page[link]` field
        if (isset($_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['data']['lot']['fields']['lot']['link'])) {
            $_['lot']['desk']['lot']['form']['lot'][1]['lot']['tabs']['lot']['data']['lot']['fields']['lot']['link']['description'] = 'This is an example description for the page link field. <a href="' . x\panel\to\link([
                'hash' => null,
                'part' => 1,
                'path' => 'asset',
                'query' => [
                    'browser' => 1,
                    'key' => 'page[link]',
                    'name' => 'set',
                    'type' => null
                ],
                'task' => 'get'
            ]) . '" onclick="return browse.call(this, 600, 300), false;" rel="opener" target="_blank">Click here</a> to insert a file.';
        }
        return $_;
    }, 10);
}