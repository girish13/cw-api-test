<?php

if(function_exists("mcrypt_encrypt")) {
    echo "mcrypt is loaded!";
} else {
    echo "mcrypt isn't loaded!";
}

?>