<?php

namespace SSZ\GitBranchCleanup\Models;

use \Carbon\Carbon;
use \SSZ\GitBranchCleanup\Libs\Helper;

class Model
{
    public function __construct(array $array = [])
    {
        foreach ($array as $property => $value) {
            if (property_exists($this, $property)) {

                $value = trim($value);

                if ($property == 'date') {
                    $this->date = Carbon::parse($value);
                    $this->date_ago = $this->date->diffForHumans();
                }

                if ($property == 'author') {
                    $this->author = $value;
                    $this->author_slug = Helper::slugify($this->author);
                }

                if ($property == 'branch') {
                    foreach ([
                        'feature',
                        'release',
                        'hotfix',
                        'master',
                        'develop',
                        'ui',
                        'review'
                    ] as $family) {
                        if (strpos($value, $family) !== false) {
                            $this->branch_family = $family;
                            break;
                        } else {
                            $this->branch_family = 'other';
                        }
                    }
                }

                $this->$property = $value;
            }
        }
    }

    public function getDate($ago = false)
    {
        $dateAgo = $this->date ? $this->date->diffForHumans() : null;
        $this->dateAgo = $dateAgo;
        return $ago ? $this->dateAgo : $this->date;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) return $this->$property;
    }

    public function toArray()
    {
        $array = [];
        foreach (get_object_vars($this) as $key => $value) {
            $array[$key] = $value;
        }
        return $array;
    }

    protected $date;
    protected $date_ago;
    protected $author;
    protected $author_slug;
    protected $branch;
    protected $branch_family;
}