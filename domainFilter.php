#!/usr/bin/php

<?php

// gotta up the default memory allotment
ini_set('memory_limit', '1024M');
$sane = true;

$emarsys_file = 'domains_hash.csv';
$client_file = 'suppression_list_domains_712828.csv';
$output_file = '111518 WebClients domains Supp File.csv';

// our emarsys list
$haystack = buildHaystack($emarsys_file);


if ($sane) {
  //var_dump(array_slice($haystack, 0, 10, true));
}

// their suppression file
$suppressions = filterList($client_file, $haystack);

outputFile($suppressions, $output_file);

echo "size of haystack:  ".count($haystack)."\n";
echo "number of suppress: ".count($suppressions)."\n";
echo "memory used: ".number_format(memory_get_peak_usage())."\n\n";

print_r($suppressions);

function buildHaystack($haystack_file) {
  global $sane;
  $haystack = array();

  $fh = fopen($haystack_file, 'r');

  // move past header line, kind of unnecessary I guess
  fgetcsv($fh);

  $count = 0;


  while (($data = fgetcsv($fh)) !== false) {
    //if ($sane && ++$count > 100) { break; }
    $email = $data[0];
    $hash = $data[1];

    // get the domain from the address
    $address = explode('@', $email);
    $domain = $address[1];

    // push hash value into domain sub-array
    $haystack[$domain][] = $hash;
  }
  fclose($fh);
  return $haystack;
}

function filterList($src_file, $haystack) {
  $suppressions = array();
  $totals = 0;

  $fh = fopen($src_file, 'r');

  while (($domain = fgets($fh)) !== false) {
    $domain = rtrim($domain);
    if (array_key_exists($domain, $haystack)) {
      //echo "$domain: ".count($haystack[$domain])."\n";
      //var_dump($haystack[$domain]);
      $totals += count($haystack[$domain]);
      //$suppressions[$domain] = true;
      $suppressions = array_merge($suppressions, $haystack[$domain]);
    }
  }
  fclose($fh);
  echo "total: $totals\n\n";
  //var_dump($suppressions);
  echo "suppressions: ".count($suppressions)."\n";
  return $suppressions;
}



function outputFile($suppressions, $target) {
  // header row
  $output = "Email Hash\n";
  $output .= implode("\n", $suppressions);
  $fh = fopen($target, 'w');
  fwrite($fh, $output);
  fclose($fh);
}


