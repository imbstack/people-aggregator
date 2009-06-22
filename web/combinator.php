<?php
/************************************************************************
 * CSS and Javascript Combinator 0.5
 * Copyright 2006 by Niels Leenheer
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * The original script was largly modified by Marek Kuziel <marek.kuziel@encode.net.nz>
 *
 * All modifications were made to make the script working for PA. Also JS packer functionality has
 * been added and the functionality is controlled by configuration values tored in
 * default_config.php
 */

require_once dirname(__FILE__)."/../config.inc";

$debug = false;
if (@$_GET['debug']) {
    $debug = true;
}
$cache = true;
$cachedir = CURRENT_THEME_FS_CACHE_PATH;

if (!isset($_GET['t']) || !isset($_GET['f'])) {
    header ("HTTP/1.0 503 Not Implemented");
    if (true === $debug) {
        header('X-Debug: missing input parameters t or f');
    }
    exit;
}

$type = $_GET['t'];
$elements_string = basename($_GET['f']);
$elements = explode(':', $elements_string);

// Determine the directory and type we should use
switch ($type) {
    case 'css':
    case 'javascript':
        $base_string_array = explode(':', $_GET['f']);
        $base_string = $base_string_array[0];
        if ('/' !== $base_string{0}) {
            $base_string = '/'.$base_string;
        }
        $base = getcwd().$base_string;
        $base_check = realpath($base);
        if (false === $base_check) {
            header ("HTTP/1.0 503 Not Implemented");
            if (true === $debug) {
                header('X-Debug: cannot find the given file base.');
            }
            exit;
        }
        $base = dirname($base);
        break;
    default:
        header ("HTTP/1.0 503 Not Implemented");
        if (true === $debug) {
            header('X-Debug: file type isn\'t JS or CSS');
        }
        exit;
};

// Determine last modification date of the files
$lastmodified = 0;
$elements_clean = $elements;
foreach ($elements as $element_id => $element) {
    $path = realpath($base . '/' . $element);
    // Double check that only js and css files are included
    if (($type == 'javascript' && substr($path, -3) != '.js') ||
        ($type == 'css' && substr($path, -4) != '.css')) {
        unset($elements_clean[$element_id]);
        continue;
    }
    // Get rid of the non-existent files from the file array
    if (substr($path, 0, strlen($base)) != $base || !file_exists($path)) {
        unset($elements_clean[$element_id]);
        continue;
    }

    $lastmodified = max($lastmodified, filemtime($path));
}
$elements = $elements_clean;

// Send Etag hash
$elements_prehash = "$base/".implode(":", $elements);
$hash = $lastmodified . '-' . md5($elements_prehash);
header ("Etag: \"" . $hash . "\"");

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
    stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) == '"' . $hash . '"') {
    // Return visit and no modifications, so do not send anything
    header ("HTTP/1.0 304 Not Modified");
    header ('Content-Length: 0');
} else {
    // First time visit or files were modified
    if ($cache) {
        // Determine supported compression method
        $gzip = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip');
        $deflate = strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate');

        // Determine used compression method
        $encoding = $gzip ? 'gzip' : ($deflate ? 'deflate' : 'none');

        // Check for buggy versions of Internet Explorer
        if (!strstr($_SERVER['HTTP_USER_AGENT'], 'Opera') &&
            preg_match('/^Mozilla\/4\.0 \(compatible; MSIE ([0-9]\.[0-9])/i', $_SERVER['HTTP_USER_AGENT'], $matches)) {
            $version = floatval($matches[1]);

            if ($version < 6) {
                $encoding = 'none';
            }
            if ($version == 6 && !strstr($_SERVER['HTTP_USER_AGENT'], 'EV1')) {
                $encoding = 'none';
            }
        }

        // Try the cache first to see if the combined files were already generated
        $cachefile = 'cache-' . $hash . '.' . $type . ($encoding != 'none' ? '.' . $encoding : '');

        if (file_exists($cachedir . '/' . $cachefile)) {
            if ($fp = fopen($cachedir . '/' . $cachefile, 'rb')) {

                if ($encoding != 'none') {
                    header ("Content-Encoding: " . $encoding);
                }

                header ("Content-Type: text/" . $type);
                header ("Content-Length: " . filesize($cachedir . '/' . $cachefile));

                fpassthru($fp);
                fclose($fp);
                exit;
            }
        }
    }

    // Decide if JS packer should be in use
    $use_js_packer_test = false;
    if (version_compare(phpversion(), "5.1.0", "ge") && "javascript" === $type) {
        require_once "ext/JavaScriptPacker/class.JavaScriptPacker.php";
        $use_js_packer_test = true;
    }
    if (false === $use_js_packer_test) {
        $use_js_packer = false;
    }
    // Get contents of the files
    $contents = '';
    $contents_packed = '';
    reset($elements);
    while (list(,$element) = each($elements)) {
        $path = realpath($base . '/' . $element);
        $contents .= "\n\n" . file_get_contents($path);
        if  (true === $use_js_packer) {
            $packer = new JavaScriptPacker($contents, 'Normal', true, false);
            $packed = $packer->pack();
            $contents_packed .= "\n\n" . $packed;
        }
    }
    if  (true === $use_js_packer) {
        $contents = $contents_packed;
    }

    // Send Content-Type
    header ("Content-Type: text/" . $type);

    if (isset($encoding) && $encoding != 'none') {
        // Send compressed contents
        $contents = gzencode($contents, 9, $gzip ? FORCE_GZIP : FORCE_DEFLATE);
        header ("Content-Encoding: " . $encoding);
        header ('Content-Length: ' . strlen($contents));
        echo $contents;
    } else {
        // Send regular contents
        header ('Content-Length: ' . strlen($contents));
        echo $contents;
    }

    // Store cache
    if ($cache) {
        if ($fp = fopen($cachedir . '/' . $cachefile, 'wb')) {
            fwrite($fp, $contents);
            fclose($fp);
        }
    }
}
?>
