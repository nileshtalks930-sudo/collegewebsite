<?php
/**
 * College CMS — public front controller
 * Document root should point to /public
 */

declare(strict_types=1);

// Bootstrap will be wired in a later step (Core App + Router).
http_response_code(200);
header('Content-Type: text/plain; charset=utf-8');
echo "College CMS — public entry (MVC scaffold ready)\n";
