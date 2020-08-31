<?php

/**
 * uses regex that accepts any word character or hyphen in last name
 *
 * @param string $name
 * @return array
 */
function anymarket_split_name($name) {
    $name = trim($name);
    $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
    $first_name = trim( preg_replace('#'.$last_name.'#', '', $name ) );
    return array($first_name, $last_name);
}
