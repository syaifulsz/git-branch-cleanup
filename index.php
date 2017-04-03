<?php

require realpath(__dir__ . '/vendor/autoload.php');

use \Carbon\Carbon;
use \Cocur\Slugify\Slugify;

class Helper
{
    public static function slugify($str, $delimiter = '-')
    {
        $slugify = new Slugify();
        return $slugify->slugify($str, $delimiter);
    }
}

class BranchModel
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

$csvParsed = array_map('str_getcsv', file(__DIR__ . '/git-branch.csv'));

if (!$csvParsed) throw new Error('No data available');

$data = [];

foreach ($csvParsed as $parse) {
    $branch = new BranchModel([
        'date' => $parse[0],
        'author' => $parse[1],
        'branch' => $parse[2]
    ]);
    $data['result'][] = $branch->toArray();

    if (!empty($data['facet']['authors'][$branch->author_slug]['branch_total'])) {
        $data['facet']['authors'][$branch->author_slug]['branch_total'] = $data['facet']['authors'][$branch->author_slug]['branch_total'] + 1;
    } else {
        $data['facet']['authors'][$branch->author_slug] = [
            'name' => $branch->author,
            'branch_total' => 1
        ];
    }

    if (!empty($data['facet']['branches'][$branch->branch_family])) {
        $data['facet']['branches'][$branch->branch_family] = $data['facet']['branches'][$branch->branch_family] + 1;
    } else {
        $data['facet']['branches'][$branch->branch_family] = 1;
    }
}

header('Content-Type: application/json'); print_r(json_encode($data)); die;