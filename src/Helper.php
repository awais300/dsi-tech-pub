<?php

namespace DSI\TechPub;

defined('ABSPATH') || exit;

/**
 * Class Helper
 * @package DSI\TechPub
 */

class Helper extends Singleton
{

    /**
     * Force downlaod a file.
     * 
     * @param  string $url
     */
    public function force_download($url)
    {
        header('Content-Description: File Transfer');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-type: application/octet-stream');
        header("Content-Disposition: attachment; filename=" . basename($url));
        ob_end_clean();
        readfile($url);
        exit;
    }
}
