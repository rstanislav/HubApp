<?php
class Authentication implements iAuthenticate {
    const KEY = 'rEsTlEr2';
    
    function __isAuthenticated() {
        return isset($_GET['key']) && $_GET['key'] == Authentication::KEY ? TRUE : FALSE;
    }
    
    function key() {
        return Authentication::KEY;
    }
}
?>