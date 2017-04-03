<?php

require realpath(__dir__ . '/vendor/autoload.php');
require realpath(__dir__ . '/libs/helper.php');
require realpath(__dir__ . '/models/model.php');

use \SSZ\GitBranchCleanup\Models\Model;
use \SSZ\GitBranchCleanup\Libs\Helper;

header('Content-Type: application/json');

if (!file_exists(__DIR__ . '/git-branch.csv'))
    echo json_encode(['git-branch.csv not found! Please run `git-branch.sh` script.']);
    die();

$csvParsed = array_map('str_getcsv', file(__DIR__ . '/git-branch.csv'));
if (!$csvParsed)
    echo json_encode(['No data is found.']);
    die();

$actionAuthor = isset($_GET['author']) && $_GET['author'] ? $_GET['author'] : null;
$data = [];
foreach ($csvParsed as $parse) {
    $branch = new Model([
        'date' => $parse[0],
        'author' => $parse[1],
        'branch' => $parse[2]
    ]);

    $data['result'][$branch->author_slug][] = $branch->toArray();

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

if ($actionAuthor) {
    $data['result'] = isset($data['result'][$actionAuthor]) ? $data['result'][$actionAuthor] : ['Author not found.'];
}

print_r(json_encode($data));
die();