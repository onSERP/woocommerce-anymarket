<?php

function anymarket_in_multidimensional_array($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && anymarket_in_multidimensional_array($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}
