<?php
// PROJECT-SPECIFIC API FUNCTIONS GO HERE

function example_echo($args) {
    return array(
        'success' => TRUE,
        'echoText' => $args['echoText'],
        );
}
?>