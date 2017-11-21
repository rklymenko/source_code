<?php
require_once 'classes/FranchiseSearch.php';

function getHelpers(){
	return array(
		'franchises_string' => file_get_contents("resource/franchises.csv"),
		'region_mappings_string' => file_get_contents("resource/region_mappings.csv")
	);
}

$helpers                = getHelpers();
$franchises_string      = $helpers['franchises_string'];
$region_mappings_string = $helpers['region_mappings_string'];

$franchise_search = new FranchiseSearch;
$franchise_search->initialize($franchises_string, $region_mappings_string);

$search_postal_code = $_POST['postal_code']; // Defaulted to 14410
$search_results = $franchise_search->search($search_postal_code);

# What PHP version is this?
echo json_encode($search_results);

?>