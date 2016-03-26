<?php

if (file_exists($f = 'lib/vendor/just-paja/fudjan/index.php')) {
	define('BASE_DIR', __DIR__);
	require_once $f;
} else {
  http_response_code(500);
  echo "Fatal error";
}
