<?php
class FranchiseSearch
{
    public $franchises = array();
    public $region_mappings = array();
    public $franchises_to_region = array();
    // Receives string in CSV format
    function initialize($franchises_string, $region_mappings_string)
    {
        // Assign $region_mappings
        // Assign $franchises
        $this->franchises = $this->csvToArray($franchises_string);
        $this->region_mappings = $this->csvToArray($region_mappings_string);
        $this->createRelationship();
    }
    
    // Should return an ordered array (by name) of franchises for this postal code with all their information
    function search($postal_code)
    {   
        // Should return array of arrays
        $reg_map_col = array_column($this->region_mappings, 'postal_code');
        $found_key = array_search($postal_code, $reg_map_col);
        if($found_key){
            if(array_key_exists($this->region_mappings[$found_key]['region_code'], $this->franchises_to_region)){
                $this->region_mappings[$found_key]['franchises'] = $this->franchises_to_region[$this->region_mappings[$found_key]['region_code']];
            }
            return $this->region_mappings[$found_key];
        }else{
            return array();
        }
    }
    
    function csvToArray($string){
        $result_data = array();
        $prepared_data = array_map('str_getcsv', str_getcsv($string,';'));
        foreach($prepared_data as $key=>$row){
            if($key > 0){
                $prepared_row = array();
                foreach($row as $k=>$val){
                     $prepared_row[$prepared_data[0][$k]] = trim($val);
                }
                $result_data[] = $prepared_row;
            }
        }
        return $result_data;
    }
    
    function createRelationship(){
        foreach($this->franchises as $franchis){
            $region_codes = explode(',', $franchis['region_codes']);
            foreach($region_codes as $reg_code){
                if(!array_key_exists($reg_code, $this->franchises_to_region)){
                    $this->franchises_to_region[$reg_code] = array();
                }
                $this->franchises_to_region[$reg_code][] = $franchis;
            }
        }
    }
}
?>