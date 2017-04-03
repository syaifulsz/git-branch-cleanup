<?php

namespace SSZ\GitBranchCleanup\Libs;

use \Cocur\Slugify\Slugify;

class Helper
{
    public static function slugify($str, $delimiter = '-')
    {
        $slugify = new Slugify();
        return $slugify->slugify($str, $delimiter);
    }
}