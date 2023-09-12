<?php

function validationNotNull($param)
{
    if (empty($param)) {
        return false;
    } else {
        return true;
    }
}
