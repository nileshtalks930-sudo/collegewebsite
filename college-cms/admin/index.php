<?php
/**
 * College CMS — admin front controller
 * Point admin vhost / path to /admin
 */

declare(strict_types=1);

// Bootstrap will be wired in a later step (Auth + AdminLTE + Router).
http_response_code(200);
header('Content-Type: text/plain; charset=utf-8');
echo "College CMS — admin entry (MVC scaffold ready)\n";
