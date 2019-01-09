#!/usr/bin/php

<?php
// gotta up the default memory allotment
ini_set('memory_limit', '3072M');


// input data, this should all be provided via user input...
$jm_contacts     = 'data/jm_contacts.csv';
$client_contacts = 'data/client_contacts.csv';
$client_domains  =  null;

$client = prompt('Name of client');
$output_file = 'output/'.date('Ymd')."_{$client}_Supp_File.csv";


// build haystack
$haystack = array();
if (isset($client_contacts) && $client_contacts) {
  echo "processing client contacts...\n";
  $haystack['contacts'] = buildHaystack($client_contacts);
}
if (isset($client_domains) && $client_domains) {
  echo "processing client domains...\n";
  $haystack['domains']  = buildHaystack($client_domains);
}


// create our suppression list
$suppressions = buildSuppressionList($jm_contacts, $haystack);
outPutFile($suppressions, $output_file);


// do some reporting...
echo "\n";
echo 'client is suppressing '.number_format(count($haystack['contacts']))." contacts\n";
echo 'client is suppressing '.number_format(count($haystack['domains']))." domains\n";
echo 'suppression list has '. number_format(count($suppressions))." hashes\n\n";
echo "memory used: ".memory_get_peak_usage()." bytes\n\n";


/*
 * *********************************************************************
 *  Functions
 * *********************************************************************
 */
function buildHaystack($client_file) {
  $hay = array();

  if (file_exists($client_file) &&
      $fh = fopen($client_file, 'r')) {
    $i = 0;
    while (($value = fgets($fh)) !== false) {
      $value = rtrim($value);
      $hay[$value] = true;
      $i++;
    }
    fclose($fh);
  }
  else {
    echo "ERROR: unable to open client contacts file!!\n";
  }

  return $hay;
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

function prompt($msg) {
  $fh = fopen('php://stdin', 'r');
  echo "$msg: ";
  $reply = trim(fgets($fh));
  fclose($fh);
  return $reply;
}

function outputFile($suppressions, $target) {
  // header row
  $output = "Email Hash\n";
  $output .= implode("\n", $suppressions);
  $fh = fopen($target, 'w');
  fwrite($fh, $output);
  fclose($fh);
}
