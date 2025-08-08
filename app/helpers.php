<?php

// Quick and dirty viewPath helper function.
function viewPath($string) { return  __DIR__ . "/views/" . $string; }

function debugData($data) { die(var_dump(print_r($data))); }