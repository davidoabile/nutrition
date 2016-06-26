<?php

try {

    apc_clear_cache();
    apc_clear_cache('user');
    apc_clear_cache('opcode');

    echo "cache cleared ok";

} catch (Exception $e) {
    
    echo "There was an error when trying to flush apc cache: " . $e->getMessage();
    
}
