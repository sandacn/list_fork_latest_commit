<?php
require __DIR__ . '/../vendor/autoload.php';


function parse_file($file) {
    $data = [];
    if (!file_exists($file)) {
        return $data;
    }

    $lines = file($file);
    foreach ($lines as $_l) {
        $_line = trim($_l);
        $_parts = explode(" ", $_line);
        $_owner = $_parts[1];
        $_repo = $_parts[3];

        $_row = [];
        $_row['owner'] = $_owner;
        $_row['repo'] = $_repo;

        $data[] = $_row;
    }
    return $data;
}

function get_first_commit($owner, $repo, &$client) {

    $commits = $client->api('repo')->commits()->all($owner, $repo, array('sha' => 'master'));
    $ret = $commits[0];
    echo $owner . "\t" . $repo . "\t" . $ret['sha'] . "\n";
    return $ret;
}


function get_batch_repo_latest_commit($client, $repos) {
    $commit_list = [];
    foreach ($repos as $_r) {
        $_commit = get_first_commit($_r['owner'], $_r['repo'], $client);
        $_row = $_r;
        $_row['commit'] = $_commit['sha'];
        $commit_list[] = $_row;
    }
    return $commit_list;
}

function get_github_client() {
    $client = new \Github\Client();
    $username = 'xxxx';
    $password = 'xxxx';
    $method = Github\Client::AUTH_HTTP_PASSWORD;
    $client->authenticate($username, $password, $method);
    return $client;
}


$options = getopt("f:");

if (!$options) {
    echo "cli parameter is empty\n";
    exit(-1);
}

$file = $options['f'];

$repo_list = parse_file($file);

$client = get_github_client();
$repo_commit_list = get_batch_repo_latest_commit($client, $repo_list);

foreach ($repo_commit_list as $_r) {
    echo implode("\t", $_r) . "\n";
}

