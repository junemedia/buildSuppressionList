#!/usr/bin/php

<?php

// gotta up the default memory allotment
ini_set('memory_limit', '3072M');

$jm_contacts = 'data/jm_contacts.csv';
$client_contacts = 'data/client_contacts.csv';
$client_domains = 'data/client_domains.csv';
$output_file = 'output/suppression_list.csv';


$haystack = buildHaystack($client_contacts, $client_domains);
$suppressions = buildSuppressionList($jm_contacts, $haystack);
outPutFile($suppressions, $output_file);

// do some reporting...
echo 'client is suppressing '.count($haystack['contacts'])." contacts\n";
echo 'client is suppressing '.count($haystack['domains'])." domains\n";
echo 'suppression list has '.count($suppressions)." hashes\n\n";

echo "memory used: ".number_format(memory_get_peak_usage())."\n\n";


function buildHaystack($contacts_file, $domains_file) {
  $contacts = array();
  $domains = array();


  if ($fh = fopen($contacts_file, 'r')) {
    while (($hash = fgets($fh)) !== false) {
      $hash = rtrim($hash);
      $contacts[$hash] = true;
    }
    fclose($fh);
  };

  if ($fh = fopen($domains_file, 'r')) {
    while (($domain = fgets($fh)) !== false) {
      $domain = rtrim($domain);
      $domains[$domain] = true;
    }
    fclose($fh);
  };

  return array(
    'contacts' => $contacts,
    'domains' => $domains
  );
}


function buildSuppressionList($needles, $haystack) {
  $hashes = array();

  if ($fh = fopen($needles, 'r')) {
    while (($contact = fgetcsv($fh)) !== false) {
      $email = $contact[2];
      $hash = $contact[3];

      $domain = explode('@', $email)[1];


      if (isset($haystack['contacts'][$hash]) ||
          isset($haystack['domains'][$domain])) {
        $hashes[] = $hash;
      }

    }
  }

  return $hashes;
}


function outputFile($suppressions, $target) {
  // header row
  $output = "Email Hash\n";
  $output .= implode("\n", $suppressions);
  $fh = fopen($target, 'w');
  fwrite($fh, $output);
  fclose($fh);
}
