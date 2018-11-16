#!/usr/bin/php

<?php

// gotta up the default memory allotment
ini_set('memory_limit', '1024M');

$filter_file = 'email_hash.txt';
$client_file = 'suppression_list_md5_emails_712828.csv';
$output_file = '111518 WebClients Supp File.csv';

// our emarsys list
$filter = getFilter($filter_file);
// their suppression file
$suppressions = filterList($client_file, $filter);
outputFile($suppressions, $output_file);

echo "number of filters:  ".count($filter)."\n";
echo "number of suppress: ".count($suppressions)."\n";
echo "memory used: ".number_format(memory_get_peak_usage())."\n\n";


//var_dump(array_slice($suppressions, 0, 10, true));


function outputFile($suppressions, $target) {
  // header row
  $output = "Email Hash\n";
  $output .= implode("\n", array_keys($suppressions));
  $fh = fopen($target, 'w');
  fwrite($fh, $output);
  fclose($fh);
}




function filterList($src_file, $filter) {
  $suppressions = array();
  $fh = fopen($src_file, 'r');
  while (($hash = fgets($fh)) !== false) {
    $hash = rtrim($hash);
    if (array_key_exists($hash, $filter)) {
      $suppressions[$hash] = true;
    }
  }
  fclose($fh);
  return $suppressions;
}


function getFilter($filter_file) {
  $filter = array();

  $fh = fopen($filter_file, 'r');

  // get rid of header line, kind of unnecessary I guess
  fgets($fh);

  while (($hash = fgets($fh)) !== false) {
    $hash = rtrim($hash);
    $filter[$hash] = true;
  }
  fclose($fh);
  return $filter;
}
