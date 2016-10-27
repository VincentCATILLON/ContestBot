<?php

require_once __DIR__.'/Ibis/Api.php';

use Devotion\VoteBot\Ibis\Api;

$options = getopt('', ['id:', 'proxy::']);
if (!array_key_exists('id', $options)) {
    throw new \Exception('The "--id" parameter must be defined.');
}

if (array_key_exists('proxy', $options)) {
    print sprintf("// Using proxy: %s\n", $options['proxy']);
    $api = new Api($options['proxy']);
} else {
    $api = new Api();
}
$creation = $api->addVoteToCreation($options['id']);
$ranking = $api->getRanking($options['id']);

print sprintf(
    "Creation \"%s\" by %s has now %s votes (ranking: %s).\n",
    $creation->Creation->Name,
    $creation->Name,
    $creation->Votes,
    $ranking
);
exit(0);
