<?php
function isPortOpen($host, $port, $timeout = 2) {
    $connection = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if (is_resource($connection)) {
        fclose($connection);
        return true;
    }
    return false;
}
?>