<?php

namespace App\Console\Commands;

use PHPAccess;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;


class ExtractDataCommand extends Command
{
    protected $signature = 'data:extract {file : The path to the MDB/ACCDB file} {columns?* : The column names}';

    protected $description = 'Extract data from MDB/ACCDB file';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        try{
            $filePath = $this->argument('file');
            $specificColumns = $this->argument('columns');
            if (!$specificColumns) {
                $specificColumns = null;
            }


            // dd($specificColumns);
            $access = new PHPAccess\PHPAccess($filePath);
            $extractedData = [];

            // Get tables in access database
            $tables = $access->getTables();
            $extractedData['tables_name'] = $tables;

            foreach ($tables as $table) {

                $tableData = $access->getData($table);


                // Check if the current table is "dt_LCMS_Patch_Processed"
                if ($table === 'dt_LCMS_Patch_Processed') {

                    $jsonFormattedData = [];
                    foreach ($tableData as $row) {
                        $jsonFormattedData[] = [
                            'AREA' => $row['AREA'],
                            'SEVERITY' => $row['SEVERITY']
                        ];
                    }
                    $tableData = $jsonFormattedData;
                } else {
                    if ($specificColumns !== null) {
                    // Filter data to keep only specified columns
                        $tableData = array_map(function ($row) use ($specificColumns) {
                            return array_intersect_key($row, array_flip($specificColumns));
                        }, $tableData);
                    }
                }

                $extractedData['tables_data'][$table] = $tableData;
            }

            $jsonFileName = storage_path('app/' . 'worksheetName' . '_data.json');
            file_put_contents($jsonFileName, json_encode($extractedData));

            // Output success message
            $this->info("Extracted data from worksheetName has been saved to $jsonFileName");


        } catch (\Exception $e) {
            // Log any exceptions or errors that occur during the extraction process
            Log::error('Error occurred during data extraction: ' . $e->getMessage());

            // Output error message
            $this->error("An error occurred during data extraction. Please check the logs for more information.");
        }

    }
}
