<?php

namespace App\Http\Controllers;

use PHPAccess\PHPAccess;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DataExtractController extends Controller
{
    public function extract(Request $request)
    {
        try{
            $filePath = $request->file;
            // $specificColumns = $this->argument('columns');
            // if (!$specificColumns) {
            //     $specificColumns = null;
            // }
            $specificColumns=null;

            // dd($specificColumns);
            $access = new PHPAccess($filePath);
            $extractedData = [];

            // Get tables in access database
            // $tables = $access->getTables();
            $tablesToQuery = [
                'dt_LCMS_Crack_Processed' => ['SEVERITY', 'AREA', 'CLASSIFICATION', 'LENGTH'],
                'dt_LCMS_Shove_Processed' => ['SHOVE_HEIGHT', 'SHOVE_WIDTH'],
                'dt_LCMS_Bleeding_Processed' => ['SEVERITY_LEFT', 'SEVERITY_RIGHT', 'BI_LEFT', 'BI_RIGHT'],
                'dt_LCMS_Patch_Processed' => ['SEVERITY', 'AREA'],
                'dt_LCMS_Rut_Processed' => ['LEFT_TYPE', 'LEFT_WIDTH', 'RIGHT_TYPE', 'RIGHT_WIDTH'],
                'dt_LCMS_Ravelling_Processed' => ['HIGH_RI_AREA_M2', 'MEDIUM_RI_AREA_M2', 'LOW_RI_AREA_M2'],
                'dt_LCMS_Pumping_Processed' => ['PUMPING_ID', 'LENGTH'],
                'dt_LCMS_Potholes_Processed' => ['SEVERITY', 'AREA'],
                // Add more tables as needed
            ];
            // $extractedData['tables_name'] = $tables;

            foreach ($tablesToQuery as $table_name=>$table_row) {
                // return $table_name;
                // return $table_row;
                // foreach($table_row as $row){
                //     return $row;
                // }
                $tableData = $access->getData($table_name);


                // Check if the current table is "dt_LCMS_Patch_Processed"
                // if ($table_name === 'dt_LCMS_Patch_Processed') {

                //     $jsonFormattedData = [];
                //     foreach ($tableData as $row) {
                //         $jsonFormattedData[] = [
                //             'AREA' => $row['AREA'],
                //             'SEVERITY' => $row['SEVERITY']
                //         ];
                //     }
                //     $tableData = $jsonFormattedData;
                // } else {
                    if ($table_row !== null) {
                    // Filter data to keep only specified columns
                        $tableData = array_map(function ($row) use ($table_row) {
                            return array_intersect_key($row, array_flip($table_row));
                        }, $tableData);
                    }
                // }

                $extractedData['tables_data'][$table_name] = $tableData;
            }
            // return $extractedData;


            // start here


            $decoded = $extractedData['tables_data'];


            if (!empty($decoded['dt_LCMS_Crack_Processed'])) {
                $pci = "1";
                // echo "The key 'dt_LCMS_Crack_Processed' exists in the decoded data.";
                // Access the 'Severity' and 'AREA' arrays
                $severityArray = array_column($decoded['dt_LCMS_Crack_Processed'], 'SEVERITY');
                $areaArray = array_column($decoded['dt_LCMS_Crack_Processed'], 'AREA');

                // Initialize an array to store unique SEVERITY values and their corresponding AREA sums
                $uniqueSeverities = [];

                // Iterate through the data
                foreach ($decoded["dt_LCMS_Crack_Processed"] as $entry) {
                    $severity = $entry["SEVERITY"];
                    $area = floatval($entry["AREA"]); // Convert AREA to float for addition

                    // If the severity is not in the unique array, add it
                    if (!array_key_exists($severity, $uniqueSeverities)) {
                        $uniqueSeverities[$severity] = $area;
                    } else {
                        // If the severity is already in the array, add the AREA value
                        $uniqueSeverities[$severity] += $area;
                    }
                }

                $Crack_High = $uniqueSeverities["Major"] ?? "0";
                $Crack_Medium = $uniqueSeverities["Medium"] ?? "0";
                $Crack_Low = $uniqueSeverities["Weak"] ?? "0";

                // Create the desired format
                // $result["dt_LCMS_Crack_Processed"] = [
                //         "Distress_Number" => $pci,
                //         "Distress_Describtion" => "Aligator Cracking",
                //         "SEVERITY" => [
                //             "High" => "$Crack_High",
                //             "Medium" => "$Crack_Medium",
                //             "Low" => "$Crack_Low"
                //         ]
                //         ];



                # Function To handle all crack type
                function Proccess_Distress($Cack_Type,$distress_descrribtionn , $pci,$unit) {
                    $pci = $pci;
                    // echo "The key 'dt_LCMS_Crack_Processed' exists in the decoded data.";
                    // Access the 'Severity' and 'AREA' arrays
                    $severityArray = array_column($Cack_Type, 'SEVERITY');
                    $areaArray = array_column($Cack_Type, $unit);

                    // Initialize an array to store unique SEVERITY values and their corresponding AREA sums
                    $uniqueSeverities = [];

                    // Iterate through the data
                    foreach ($Cack_Type as $entry) {
                        $severity = $entry["SEVERITY"];
                        $area = floatval($entry[$unit]); // Convert AREA to float for addition

                        // If the severity is not in the unique array, add it
                        if (!array_key_exists($severity, $uniqueSeverities)) {
                            $uniqueSeverities[$severity] = ($area );
                        } else {
                            // If the severity is already in the array, add the AREA value
                            $uniqueSeverities[$severity] += ($area );
                        }
                    }

                    $Crack_Transversal_High = $uniqueSeverities["Major"] ?? "0";
                    $Crack_Transversal_Medium = $uniqueSeverities["Medium"] ?? "0";
                    $Crack_Transversal_Low = $uniqueSeverities["Weak"] ?? "0";

                    // Create the desired format
                    $result = [
                            "Distress_Number" => $pci,
                            "Distress_Describtion" => "$distress_descrribtionn",
                            "SEVERITY" => [
                                "High" => "$Crack_Transversal_High",
                                "Medium" => "$Crack_Transversal_Medium",
                                "Low" => "$Crack_Transversal_Low"
                            ]
                            ];

                return $result;
                }

                # Function To handle all crack type
                function Proccess_Distress_Transversal_Longitudinal($Cack_Type,$distress_descrribtionn , $pci,$unit) {
                    $pci = $pci;
                    // echo "The key 'dt_LCMS_Crack_Processed' exists in the decoded data.";
                    // Access the 'Severity' and 'AREA' arrays
                    $severityArray = array_column($Cack_Type, 'SEVERITY');
                    $areaArray = array_column($Cack_Type, $unit);

                    // Initialize an array to store unique SEVERITY values and their corresponding AREA sums
                    $uniqueSeverities = [];

                    // Iterate through the data
                    foreach ($Cack_Type as $entry) {
                        $severity = $entry["SEVERITY"];
                        $area = floatval($entry[$unit]); // Convert AREA to float for addition

                        // If the severity is not in the unique array, add it
                        if (!array_key_exists($severity, $uniqueSeverities)) {
                            $uniqueSeverities[$severity] = ($area /1000);
                        } else {
                            // If the severity is already in the array, add the AREA value
                            $uniqueSeverities[$severity] += ($area /1000);
                        }
                    }

                    $Crack_Transversal_High = $uniqueSeverities["Major"] ?? "0";
                    $Crack_Transversal_Medium = $uniqueSeverities["Medium"] ?? "0";
                    $Crack_Transversal_Low = $uniqueSeverities["Weak"] ?? "0";

                    // Create the desired format
                    $result = [
                            "Distress_Number" => $pci,
                            "Distress_Describtion" => "$distress_descrribtionn",
                            "SEVERITY" => [
                                "High" => "$Crack_Transversal_High",
                                "Medium" => "$Crack_Transversal_Medium",
                                "Low" => "$Crack_Transversal_Low"
                            ]
                            ];

                return $result;
                }


                # Classify All Crack Type

                    $data = $decoded["dt_LCMS_Crack_Processed"];
                    // Initialize an associative array to store data based on CLASSIFICATION
                    $classifiedData = array();
                    // Iterate through the original array
                    foreach ($data as $item) {
                        // Get the CLASSIFICATION value
                        $classification = $item['CLASSIFICATION'];
                        // If the array for this CLASSIFICATION doesn't exist, create it
                        if (!isset($classifiedData[$classification])) {
                            $classifiedData[$classification] = array();
                        }

                        // Add the item to the array for this CLASSIFICATION
                        $classifiedData[$classification][] = $item;
                    };

                    // var_dump($classifiedData);

                # Identify each crack type
                    // $Transversal_CRACK = $classifiedData["Transversal"];
                    // $Longitudinal_CRACK = $classifiedData["Longitudinal"];
                    // $Edge_crack_CRACK = $classifiedData["Edge crack"];
                    // $Slippage_cracking_CRACK = $classifiedData["Slippage cracking"];
                    // $Transversal_CRACK = $classifiedData["Transversal"];
                    // $Alligator_Crack_CRACK = $classifiedData["Alligator Crack Region 1"];
                    // $Multiple_Crack_CRACK = $classifiedData["Multiple Crack Region 1"];

                # Handle Edge_CRACK
                    if(isset($classifiedData["Edge crack"])){
                        $Edge_crack_CRACK = $classifiedData["Edge crack"];
                        $result["dt_LCMS_Crack_Edge_Processed"] =Proccess_Distress($Edge_crack_CRACK,"Transversal" , 7,"LENGTH");
                        // var_dump($Edge_crack_CRACK);
                    }else{
                        // echo "Edge crack Nor Found";
                    }


                # Handle Transversal Crack
                    if(isset($classifiedData["Transversal"])){
                        $Transversal_crack_CRACK = $classifiedData["Transversal"];
                        $result["dt_LCMS_Crack_Transversal_Processed"] =Proccess_Distress_Transversal_Longitudinal($Transversal_crack_CRACK,"Transversal" , 8 , "LENGTH");
                        // var_dump($result["dt_LCMS_Crack_Transversal_Processed"]);
                    }else{
                        // echo "Transversal crack Not Found";
                    }
                    // var_dump($classifiedData);
                # Handle Longitudinal_CRACK
                    // Merge Longitudinal and Unclassified in one array to Sum it as one PCI
                    if(isset($classifiedData["Longitudinal"])){
                        $Merge_Longitudinal_and_Unclissifed_CRACK = array_merge($classifiedData["Unclassified"], $classifiedData["Longitudinal"]);
                        $Longitudinal_and_Unclissifed_CRACK = $Merge_Longitudinal_and_Unclissifed_CRACK;
                        $result["t_LCMS_Crack_Longitudinal_Processed"] =Proccess_Distress_Transversal_Longitudinal($Longitudinal_and_Unclissifed_CRACK,"Longitudinal" , 10 , "LENGTH");
                        // var_dump($result["t_LCMS_Crack_Longitudinal_Processed"]);
                    }else{
                        // Set the Unclassified in place of Longitudinal
                        // echo " Longitudinal Not Found";

                        $Unclissifed_CRACK = $classifiedData["Unclassified"];
                        $result["t_LCMS_Crack_Longitudinal_Processed"] =Proccess_Distress_Transversal_Longitudinal($Unclissifed_CRACK,"Longitudinal" , 10 , "LENGTH");
                    }

                # Handle Slippage_crack
                    if(isset($classifiedData["Slippage cracking"])){
                        $Slippage_crack_CRACK = $classifiedData["Slippage cracking"];
                        $result["t_LCMS_Slippag_Cracking_Processed"] =Proccess_Distress($Slippage_crack_CRACK,"Slippage cracking" , 17 , "AREA");
                        // var_dump($result["t_LCMS_Slippag_Cracking_Processed"]);
                    }else{
                        // echo "Edge Slippage cracking Nor Found";
                    }

                # Handle Alligator Crack Region
                    # Group All Alligator Crack Regions in one array
                    $Alligator_Crack_Regions = [];

                    foreach ($classifiedData as $key => $subArray) {
                        if (strpos($key, "Alligator Crack Region") !== false) {
                            $Alligator_Crack_Regions = array_merge($Alligator_Crack_Regions, $subArray);
                        }
                    }

                    // To check if mdb have Alligator_Crack_Regions
                    if(!empty($Alligator_Crack_Regions)){
                        $Alligator_Crack_Region = $Alligator_Crack_Regions;
                        $result["t_LCMS_Alligator_Crack_Processed"] =Proccess_Distress($Alligator_Crack_Region,"Alligator Crack Region" , 1 , "AREA");
                        // var_dump($result["t_LCMS_Alligator_Crack_Processed"]);
                    }else{
                        // echo "Edge Alligator Crack Region 1 Crack Region 1 Nor Found";
                    }


                # Handle Multiple Crack Regions

                    # Group All Multiple Crack Regions in one array
                    $Multiple_Crack_Regions = [];

                    foreach ($classifiedData as $key => $subArray) {
                        if (strpos($key, "Multiple Crack Region") !== false) {
                            $Multiple_Crack_Regions = array_merge($Multiple_Crack_Regions, $subArray);
                        }
                    }
                    // Check i mdb have Multiple_Crack_Regions
                    if(!empty($Multiple_Crack_Regions)){
                        $Multiple_Crack = $Multiple_Crack_Regions;
                        $result["t_LCMS_Multiple_Crack_Processed"] =Proccess_Distress($Multiple_Crack,"Multiple Crack Region" , 3 , "AREA");
                        // var_dump($result["t_LCMS_Multiple_Crack_Processed"]);
                    }else{
                        // echo "Multiple Crack Region 1 Not Found";
                    }

                    // var_dump($classifiedData["Unclassified"]);
                    // var_dump($classifiedData["Longitudinal"]);
                    // $mergedArray = array_merge($classifiedData["Unclassified"], $classifiedData["Longitudinal"]);
                    // var_dump($mergedArray);


                # Debug Data
                    // var_dump($classifiedData);
                    // var_dump($classifiedData["Multiple Crack Region 1"]);
                    // var_dump($classifiedData["Longitudinal"]);
                    // var_dump($classifiedData["Transversal"]);
                    // var_dump($classifiedData["Edge crack"]);
                    // var_dump($classifiedData["Slippage cracking"]);
                    // var_dump($classifiedData["Alligator Crack Region 1"]);


                }else{
                    // echo("dt_LCMS_Crack_Processed م موجوووود ");
                }










            if(!empty($decoded["dt_LCMS_Potholes_Processed"])){

                $pci = "13";
                // echo "The key 'dt_LCMS_Potholes_Processed' exists in the decoded data.";
                // Access the 'Severity' and 'AREA' arrays
                $severityArray = array_column($decoded['dt_LCMS_Potholes_Processed'], 'SEVERITY');
                $areaArray = array_column($decoded['dt_LCMS_Potholes_Processed'], 'AREA');

                // Initialize an array to store unique SEVERITY values and their corresponding AREA sums
                $uniqueSeveritiesd = [];

                // Iterate through the data
                foreach ($decoded["dt_LCMS_Potholes_Processed"] as $entry) {
                    $severity = $entry["SEVERITY"];
                    $area = floatval($entry["AREA"]); // Convert AREA to float for addition

                    // If the severity is not in the unique array, add it
                    if (!array_key_exists($severity, $uniqueSeveritiesd)) {
                        $uniqueSeveritiesd[$severity] = $area;
                    } else {
                        // If the severity is already in the array, add the AREA value
                        $uniqueSeveritiesd[$severity] += $area;
                    }
                }

                $Potholes_High = $uniqueSeveritiesd["High\n"]  ?? "0";
                $Potholes_Medium = $uniqueSeveritiesd["Moderate\n"]  ?? "0";
                $Potholes_Low = $uniqueSeveritiesd["Low\n"]  ?? "0";
                // $Potholes_High = $uniqueSeveritiesd["High\n"]  ?? "0";
                // $Potholes_Medium = $uniqueSeveritiesd["Moderate\n"]  ?? "0";
                // $Potholes_Low = $uniqueSeveritiesd["Low\n"]  ?? "0";

                // Create the desired format
                $result["dt_LCMS_Potholes_Processed"] = [
                        "Distress_Number" => $pci,
                        "Distress_Describtion" => "Potholes",
                        "SEVERITY" => [
                            "High" => "$Potholes_High",
                            "Medium" => "$Potholes_Medium",
                            "Low" => "$Potholes_Low"
                        ]
                    ];

            }else{
                // $result["dt_LCMS_Potholes_Processed"] = NULL;
            }

            # Handle Patch Distress
            if(!empty($decoded["dt_LCMS_Patch_Processed"])){

                $pci = "11";

                // Access the 'Severity' and 'AREA' arrays
                $severityArray = array_column($decoded['dt_LCMS_Patch_Processed'], 'SEVERITY');
                $areaArray = array_column($decoded['dt_LCMS_Patch_Processed'], 'AREA');

                // Initialize an array to store unique SEVERITY values and their corresponding AREA sums
                $uniqueSeverities = [];

                // Iterate through the data
                foreach ($decoded["dt_LCMS_Patch_Processed"] as $entry) {
                    $severity = $entry["SEVERITY"];
                    $area = floatval($entry["AREA"]); // Convert AREA to float for addition

                    // If the severity is not in the unique array, add it
                    if (!array_key_exists($severity, $uniqueSeverities)) {
                        $uniqueSeverities[$severity] = $area;
                    } else {
                        // If the severity is already in the array, add the AREA value
                        $uniqueSeverities[$severity] += $area;
                    }
                }

                $Patch_High = $uniqueSeverities["High\n"] ?? "0";
                $Patch_Medium = $uniqueSeverities["Medium\n"] ?? "0";
                $Patch_Low = $uniqueSeverities["Low\n"] ?? "0";


                // Create the desired format
                $result["dt_LCMS_Patch_Processed"] = [
                        "Distress_Number" => $pci,
                        "Distress_Describtion" => "Patching",
                        "SEVERITY" => [
                            "High" => "$Patch_High",
                            "Medium" => "$Patch_Medium",
                            "Low" => "$Patch_Low"
                        ]
                        ];



            }else{
                // $result["dt_LCMS_Patch_Processed"] = NULL;
            }

            if(!empty($decoded["dt_LCMS_Shove_Processed"])){
                $pci = "16";
                $severityArray = array_column($decoded['dt_LCMS_Shove_Processed'], 'SHOVE_HEIGHT');
                $areaArray = array_column($decoded['dt_LCMS_Shove_Processed'], 'SHOVE_WIDTH');

                $newArray = array();

                foreach ($severityArray as $value) {
                    $floatValue = floatval($value); // Convert string to float for numeric comparison

                    if ($floatValue >= 6 && $floatValue < 13) {
                        $new_value = 'Low';
                    } else if ($floatValue >= 13 && $floatValue < 25) {
                        $new_value = 'Medium';
                    } else if ($floatValue >= 25) {
                        $new_value = 'High';
                    } else {
                        // If none of the conditions are met, keep the original value
                        $new_value = $value;
                    }

                    $converted_severity[] = $new_value;
                }

                #Group tow aays in one array
                    $Shove_Data = array_map(function($converted_severity, $areaArray) {
                        return array("SEVERITY" => $converted_severity, "AREA" => $areaArray);
                    }, $converted_severity, $areaArray);

                // Initialize an array to store unique SEVERITY values and their corresponding AREA sums
                $uniqueSeverities = [];

                // Iterate through the data
                foreach ($Shove_Data as $entry) {
                    $severity = $entry["SEVERITY"];
                    $area = floatval($entry["AREA"]); // Convert AREA to float for addition

                    // If the severity is not in the unique array, add it
                    if (!array_key_exists($severity, $uniqueSeverities)) {
                        $uniqueSeverities[$severity] = ($area / 1000) ;
                    } else {
                        // If the severity is already in the array, add the AREA value
                        $uniqueSeverities[$severity] += ($area / 1000);
                    }
                }
                $Shove_High = ($uniqueSeverities["High"])  ?? "0";
                $Shove_Medium = ($uniqueSeverities["Medium"])  ?? "0";
                $Shove_Low = ($uniqueSeverities["Low"])  ?? "0";

                // Create the desired format
                $result["dt_LCMS_Shove_Processed"] = [
                    "Distress_Number" => $pci,
                    "Distress_Describtion" => "Shoving",
                    "SEVERITY" => [
                        "High" => "$Shove_High",
                        "Medium" => "$Shove_Medium",
                        "Low" => "$Shove_Low"
                    ]
                ];

                // return $result;
              }else{
                // $result["dt_LCMS_Shove_Processed"] = NULL;
            }

            // Handle
            if(!empty($decoded["dt_LCMS_Ravelling_Processed"])){
                $pci = 19;

                $Severity_High = array_column($decoded['dt_LCMS_Ravelling_Processed'], 'HIGH_RI_AREA_M2');
                $Sevirity_Meduim = array_column($decoded['dt_LCMS_Ravelling_Processed'], 'MEDIUM_RI_AREA_M2');
                $Sevirity_Low = array_column($decoded['dt_LCMS_Ravelling_Processed'], 'LOW_RI_AREA_M2');


                $Sum_Severity_High = array_sum($Severity_High)  ?? "0";
                $Sum_Sevirity_Meduim = array_sum($Sevirity_Meduim) ?? "0";
                $Sum_Sevirity_Low = array_sum($Sevirity_Low) ?? "0";

                $keys = array("Medium", "Weak", "Very Weak", "Major");
                $values = array(0.078, 0.357, 0.078, 0);

                // Combine keys and values to create the array
                $resultArray = array_combine($keys, $values);


                // Store the result
                $result["dt_LCMS_Ravelling_Processed"] = [
                    "Distress_Number" => $pci,
                    "Distress_Describtion" => "Raveling",
                    "SEVERITY" => [
                        "High" => "$Sum_Severity_High",
                        "Medium" => "$Sum_Sevirity_Meduim",
                        "Low" => "$Sum_Sevirity_Low"
                    ]
                ];


            }else{
                // $result["dt_LCMS_Ravelling_Processed"] = NULL;
            }






            if(!empty($decoded["dt_LCMS_Rut_Processed"])){

                $pci = 15;


                $LEFT_TYPE = array_column($decoded['dt_LCMS_Rut_Processed'], 'LEFT_TYPE');
                $RIGHT_TYPE = array_column($decoded['dt_LCMS_Rut_Processed'], 'RIGHT_TYPE');
                $LEFT_WIDTH = array_column($decoded['dt_LCMS_Rut_Processed'], 'LEFT_WIDTH');
                $RIGHT_WIDTH = array_column($decoded['dt_LCMS_Rut_Processed'], 'RIGHT_WIDTH');

                $maxValues = array();
                $sourceArrays = array();
                $widthValues = array();

                // Loop through each position in the arrays
                for ($i = 0; $i < count($LEFT_TYPE); $i++) {
                    // Compare the values at the current position and take the maximum
                    $maxValue = max($LEFT_TYPE[$i], $RIGHT_TYPE[$i]);
                    $maxValues[] = $maxValue;

                    // Determine the source array for each maximum value
                    $sourceArrays[] = ($LEFT_TYPE[$i] >= $RIGHT_TYPE[$i]) ? 'LEFT_TYPE' : 'RIGHT_TYPE';

                    // Associate the width value based on the source array
                    $widthValues[] = ($sourceArrays[$i] === 'LEFT_TYPE') ? $LEFT_WIDTH[$i] : $RIGHT_WIDTH[$i];
                }

                // Get unique values of $maxValues
                $uniqueMaxValues = array_unique($maxValues);

                // Initialize variables to store sums for each unique maximum value
                $sumVariables = array();
                foreach ($uniqueMaxValues as $uniqueValue) {
                    ${'sum_of_' . $uniqueValue} = 0;
                    $sumVariables[] = &${'sum_of_' . $uniqueValue};
                }

                // Calculate sums for each unique maximum value
                foreach ($maxValues as $key => $value) {
                    ${'sum_of_' . $value} += $widthValues[$key];
                }

                $uniqueValueSums = [];

                // Output the sums for each unique maximum value
                foreach ($uniqueMaxValues as $uniqueValue) {
                    // Store the value and its sum in the array
                    $uniqueValueSums[$uniqueValue] = ${'sum_of_' . $uniqueValue};

                }

                $Rut_High = isset($uniqueValueSums[3]) ? ($uniqueValueSums[3] / 1000) : "0";
                $Rut_Medium = isset($uniqueValueSums[2]) ? ($uniqueValueSums[2] / 1000) : "0";
                $Rut_Low = isset($uniqueValueSums[1]) ? ($uniqueValueSums[1] / 1000) : "0";

                // Store the result
                $result["dt_LCMS_Rut_Processed"] = [
                    "Distress_Number" => $pci,
                    "Distress_Describtion" => "Rutting",
                    "SEVERITY" => [
                        "High" => "$Rut_High",
                        "Medium" => "$Rut_Medium",
                        "Low" => "$Rut_Low"
                    ]
                ];


            }else{
                // $result["dt_LCMS_Rut_Processed"] = NULL;
            }


            if (!empty($decoded["dt_LCMS_Bleeding_Processed"])) {
                $Bleeding_Data = $decoded["dt_LCMS_Bleeding_Processed"];
                $pci = 2;

                // Identify Important values
                $severityRight = array_column($decoded['dt_LCMS_Bleeding_Processed'], 'SEVERITY_LEFT');
                $severityLeft = array_column($decoded['dt_LCMS_Bleeding_Processed'], 'SEVERITY_RIGHT');

                // Step 1: Loop through each array and convert SEVERITY_LEFT and SEVERITY_RIGHT to specific values
                $severityMapping = [
                    "High Bleeding" => 3,
                    "Medium Bleeding" => 2,
                    "Light Bleeding" => 1,
                    "No Bleeding" => 0,
                ];

                // Initialize variables to store unique max severity values and their counts
                $Bleeding_0 = 0;
                $Bleeding_1 = 0;
                $Bleeding_2 = 0;
                $Bleeding_3 = 0;

                foreach ($Bleeding_Data as &$inputArray) {
                    $severityLeftValue = $severityMapping[$inputArray["SEVERITY_LEFT"]];
                    $severityRightValue = $severityMapping[$inputArray["SEVERITY_RIGHT"]];

                    // Step 2: Take the max value of SEVERITY_LEFT and SEVERITY_RIGHT
                    $maxSeverity = max($severityLeftValue, $severityRightValue);

                    // Increment the count of the corresponding variable
                    ${"Bleeding_" . $maxSeverity}++;
                }

                // Now $Bleeding_0, $Bleeding_1, $Bleeding_2, $Bleeding_3 contain the count of occurrences of each max severity value.

                // Identify Severity values
                $Bleed_High = ($Bleeding_3 * 0.375) ?? "0";
                $Bleed_Medium = ($Bleeding_2 * 0.375) ?? "0";
                $Bleed_Low = ($Bleeding_1 * 0.375) ?? "0";

                // Create the desired format
                $result["dt_LCMS_Bleeding_Processed"] = [
                        "Distress_Number" => $pci,
                        "Distress_Describtion" => "Bleeding",
                        "SEVERITY" => [
                            "High" => "$Bleed_High",
                            "Medium" => "$Bleed_Medium",
                            "Low" => "$Bleed_Low"
                        ]
                    ];
            }else{
                    // $result["dt_LCMS_Bleeding_Processed"] = NULL;
                }





            if (!empty($decoded['dt_LCMS_Pumping_Processed'])) {
                $pci = "4";
                // Access the 'Severity' and 'AREA' arrays
                $severityArray = array_column($decoded['dt_LCMS_Pumping_Processed'], 'PUMPING_ID');
                $areaArray = array_column($decoded['dt_LCMS_Pumping_Processed'], 'LENGTH');

                // Initialize an array to store unique SEVERITY values and their corresponding AREA sums
                $uniqueSeverities = [];
                // $uniqueSeverities[] = "2";

                // Iterate through the data
                foreach ($decoded["dt_LCMS_Pumping_Processed"] as $entry) {
                    $severity = $entry["PUMPING_ID"];
                    $area = floatval($entry["LENGTH"]); // Convert AREA to float for addition

                    // If the severity is not in the unique array, add it
                    if (!array_key_exists($severity, $uniqueSeverities)) {
                        $uniqueSeverities[$severity] = $area;
                    } else {
                        // If the severity is already in the array, add the AREA value
                        $uniqueSeverities[$severity] += $area;
                    }
                }
                // var_dump($uniqueSeverities);
                // $uniqueSeverities["2"] = "5";
                // $uniqueSeverities = $uniqueSeverities["2"] ?? 'default_value';
                $Pump_High = $uniqueSeverities["2"] ?? "0";
                $Pump_Medium = $uniqueSeverities["1"] ?? "0";
                $Pump_Low = $uniqueSeverities["0"] ?? "0";


                // Create the desired format
                $result["dt_LCMS_Pumping_Processed"] = [
                    "Distress_Number" => $pci,
                    "Distress_Describtion" => "Bump & Sags",
                    "SEVERITY" => [
                        "High" => "$Pump_High",
                        "Medium" => "$Pump_Medium",
                        "Low" => "$Pump_Low"
                    ]
                ];


            }else{
                // echo("dt_LCMS_Pumping_Processed م موجوووود ");
            }

            foreach ($result as $key => $value) {
                // var_dump($value['SEVERITY']);
                $result[$key]['SEVERITY'] = array_filter($value['SEVERITY']);
                $result[$key] = array_filter($result[$key]);
                if(!isset($result[$key]['SEVERITY'])){
                    unset($result[$key]);
                }
                // var_dump($result);
                    // if ($value['SEVERITY']['High'] == 0) {
                    //     $result[$key]['SEVERITY'] = [
                    //         "Medium" => $value['SEVERITY']['Medium'],
                    //         "Low" => $value['SEVERITY']['Low']
                    //     ];
                    // }
                    // if ($value['SEVERITY']['Medium']  == 0) {
                    //     $result[$key]['SEVERITY'] = [
                    //         "High" => $value['SEVERITY']['High'],
                    //         "Low" => $value['SEVERITY']['Low']
                    //     ];
                    // }
                    // if ($value['SEVERITY']['Low'] == 0) {
                    //     $result[$key]['SEVERITY'] = [
                    //         "High" => $value['SEVERITY']['High'],
                    //         "Medium" => $value['SEVERITY']['Medium'],
                    //     ];
                    // }
            }




          return  $json = json_encode($result, JSON_PRETTY_PRINT);

            // end here

            $jsonFileName = storage_path('app/' . 'New-worksheetName' . '_data.json');
            file_put_contents($jsonFileName, json_encode($extractedData));

            // Output success message
            return response()->json([
                'message' => "Extracted data from worksheetName has been saved to $jsonFileName",
                'file' => $jsonFileName
            ]);


        } catch (\Exception $e) {
            // Log any exceptions or errors that occur during the extraction process
            Log::error('Error occurred during data extraction: ' . $e->getMessage());

            // Output error message
            return response()->json([
                'error' => "An error occurred during data extraction. Please check the logs for more information."
            ], 500);

        }
    }
}
