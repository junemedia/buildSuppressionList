#!/usr/bin/php

<?php

// gotta up the default memory allotment
ini_set('memory_limit', '3072M');

$jm_contacts     = 'data/jm_contacts.csv';
$client_contacts = 'data/client_contacts.csv';
$client_domains  = 'data/client_domains.csv';
$output_file     = 'output/suppression_list.csv';



$haystack = buildHaystack($client_contacts, $client_domains);
$suppressions = buildSuppressionList($jm_contacts, $haystack);
outPutFile($suppressions, $output_file);

// do some reporting...
echo "\n";
echo 'client is suppressing '.count($haystack['contacts'])." contacts\n";
echo 'client is suppressing '.count($haystack['domains'])." domains\n";
echo 'suppression list has '.count($suppressions)." hashes\n\n";

echo "memory used: ".number_format(memory_get_peak_usage())."\n\n";


function buildHaystack($contacts_file = NULL, $domains_file = NULL) {
  $contacts = array();
  $domains = array();


  if ($contacts_file !== NULL) {
    if (file_exists($contacts_file) &&
        $fh = fopen($contacts_file, 'r')) {
      echo 'processing client contacts...';
      $i = 0;
      while (($hash = fgets($fh)) !== false) {
        $hash = rtrim($hash);
        $contacts[$hash] = true;
        $i++;
      }
      fclose($fh);
      echo "$i contacts processed";
    }
    else {
      echo 'ERROR: unable to open client contacts file!!';
    }
  }
  else {
    echo 'no client contacts to process';
  }
  echo "\n";

  if ($domains_file !== NULL) {
    if (file_exists($domains_file) &&
        $fh = fopen($domains_file, 'r')) {
      echo 'processing client domains...';
      $i = 0;
      while (($domain = fgets($fh)) !== false) {
        $domain = rtrim($domain);
        $domains[$domain] = true;
        $i++;
      }
      fclose($fh);
      echo "$i domains processed";
    }
    else {
      echo 'ERROR: unable to open client contacts file!!';
    }
  }
  else {
    echo 'no client domains to process';
  }
  echo "\n";

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
