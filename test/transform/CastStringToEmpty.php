<?php

namespace transform;

trait CastStringToEmpty
{
    /**
     * @Transform /^\-\-\-$/
     */
    public function castStringToEmpty($string)
    {
        return "";
    }
}
