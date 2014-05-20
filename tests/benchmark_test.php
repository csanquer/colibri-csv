#!/usr/bin/env php
<?php
include __DIR__.'/../vendor/autoload.php';

use Faker\Factory;
use CSanquer\ColibriCsv\CsvReader;
use CSanquer\ColibriCsv\CsvWriter;
use Symfony\Component\Stopwatch\Stopwatch;

$dir = __DIR__.'/tmp';
if (!is_dir($dir)) {
    mkdir($dir);
}

if ($argc > 1) {
    $maxLines = abs((int) $argv[1]);
} else {
    $maxLines = 100000;
}

echo 'Generating a CSV with '.$maxLines.' lines'."\n\n";

$file1 = $dir.'/bench'.$maxLines.'.csv';

$watch = new Stopwatch();

$faker = Factory::create('fr_FR');

$csvWriter1 = new CsvWriter(array(
    'delimiter' => ';',
    'enclosure' => '"',
    'encoding' => 'CP1252',
    'eol' => "\r\n",
    'escape' => "\\",
));

$csvWriter1->open($file1);

$watch->start('csv_generation');

$header = array(
    'lastname',
    'firstname',
    'phone number',
    'email',
    'birthday',
    'address',
    'zipcode',
    'city',
    'points',
);

$csvWriter1->writeRow($header);

for ($i = 0; $i < $maxLines; $i++) {
    $lastname = $faker->lastName;
    $firstname = $faker->firstName;
    $row = array(
        $lastname,
        $firstname,
        $faker->phoneNumber,
        strtolower($firstname.'.'.$lastname).'@'.$faker->domainName,
        $faker->dateTimeThisCentury->format('Y-m-d'),
        $faker->streetAddress,
        $faker->postcode,
        $faker->city,
        $faker->randomNumber(),
    );

    $csvWriter1->writeRow($row);
}

$csvWriter1->close();
$eventGeneration = $watch->stop('csv_generation');

echo 'Converting the CSV'."\n\n";
$file2 = $dir.'/bench_result'.$maxLines.'.csv';
$watch->start('csv_convert');

$csvReader = new CsvReader(array(
    'delimiter' => ';',
    'enclosure' => '"',
    'encoding' => 'CP1252',
    'eol' => "\r\n",
    'escape' => "\\",
));
$csvReader->open($file1);

$csvWriter2 = new CsvWriter(array(
    'delimiter' => ',',
    'enclosure' => '"',
    'encoding' => 'UTF-8',
    'eol' => "\n",
    'escape' => "\\",
));
$csvWriter2->open($file2);

foreach ($csvReader as $row) {
    $csvWriter2->writeRow($row);
}

$csvWriter2->close();
$csvReader->close();
$eventConverting = $watch->stop('csv_convert');

echo 'Results'."\n";
echo 'CSV Generation'."\n\n";
echo 'duration     = '.$eventGeneration->getDuration(). ' ms'."\n";
echo 'memory usage = '.($eventGeneration->getMemory()/(1024*1024)).' Mb ('.$eventConverting->getMemory().' b)'."\n\n";
echo 'CSV Parsing/Converting'."\n\n";
echo 'duration     = '.$eventConverting->getDuration(). ' ms'."\n";
echo 'memory usage = '.($eventConverting->getMemory()/(1024*1024)).' Mb ('.$eventConverting->getMemory().' b)'."\n";
