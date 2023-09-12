<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') or define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  or define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') or define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   or define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  or define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           or define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     or define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       or define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  or define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   or define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              or define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            or define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       or define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        or define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          or define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         or define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   or define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  or define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') or define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     or define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       or define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      or define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      or define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

// URL FILE
define('URL_IMAGE_PRODUK', 'https://andipublisher.com/images/barang/');
define('URL_IMAGE_BANNER', 'https://dev.andipublisher.com/images/banner/');
define('URL_IMAGE_BLOG', 'https://andipublisher.com/images/blog/');
define('URL_IMAGE_PROFIL', 'https://andipublisher.com/images/user/profile/');

// RAJAONGKIR
define('RAJAONGKIR_KEY', '939bcca3d01d3fc6a78db15d7c95b3e3');
define('RAJAONGKIR_ORIGIN_TYPE', 'subdistrict');
define('RAJAONGKIR_ORIGIN', '5781');
define('RAJAONGKIR_DESTINATION_TYPE', 'subdistrict'); // JANGAN DIUBAH
define('RAJAONGKIR_KURIR', 'jne:jnt:pos:tiki:wahana:sicepat');

// COURIER ANTER AJA
define('ANTERAJA_BASEPATH', 'https://doit-sit.anteraja.id/andi');
define('ANTERAJA_ACCESS_KEY_ID', 'Anteraja_x_andi_SIT');
define('ANTERAJA_SECRET_ACCESS_KEY', 'SYRzSiaro+a9U0wa5munOw==');

// COURIER JNE
// define('JNE_BASEPATH', 'https://apiv2.jne.co.id:10205/tracing/api/');
define('JNE_BASEPATH', 'http://apiv2.jne.co.id:10101/tracing/api/');
define('JNE_USERNAME', 'CVANDI');
define('JNE_API_KEY', '35f0c9c8b5e8677f5e6495282ca2cd54');

//COURIER SICEPAT
define('SICEPAT_BASEPATH', 'https://apitrek.sicepat.com/');
define('SICEPAT_APIKEY_DEV', '9488C9FAE0F344EFA2A02DF9681EF83D'); // API Key Development (Pickup)
define('SICEPAT_APIKEY_PROD', 'e9ac5cd70b5ef79c55a220a38eea622c'); // API Key Production (Tracking)

// COURIER LIONPARCEL
define('LIONPARCEL_AUTH', 'Basic bGlvbnBhcmNlbDpsaW9ucGFyY2VsQDEyMw==');

// MIDTRANS
define('MTRANS_MERCHANT_ID', 'G331133469'); // SANDBOX
define('MTRANS_CLIENT_KEY', 'SB-Mid-client-JcXOnzOea_l7GWnm'); // SANDBOX
define('MTRANS_SERVER_KEY', 'SB-Mid-server-KKdX8NsZQgRHvo8ySXJLsXs8'); // SANDBOX
define('MTRANS_PATH_DEV', 'https://app.sandbox.midtrans.com/snap/v1/');
define('MTRANS_PATH', 'https://app.sandbox.midtrans.com/snap/v1/transactions');
