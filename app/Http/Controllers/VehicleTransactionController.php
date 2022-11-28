<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Interchange;
use Illuminate\Http\Request;
use App\Models\VehicleTransaction;
use Illuminate\Support\Facades\Validator;
use stdClass;

class VehicleTransactionController extends Controller
{
    // get insterchange data from table
    function getFormData()
    {
        try{
            return Interchange::select('id', 'name', 'km')->get();
        } catch(\Exception $e){
            return response()->json(['error', $e->getMessage()], 500);
        }
    }

    // save vehicle entry and exit also calculate toll tax if vehicle exits
    function save(Request $request)
    {
        try{
            $data = json_decode(json_encode($request->data));
            // vehicle entry only
            if($request->type == 'entry'){
                // if vehicle already exists and has no exit, return error message
                if(VehicleTransaction::where('number_plate', $data->number_plate)->whereNull('exit_interchange_id')->exists()){
                    return response()->json(['error' => 'Vehicle already entered and no exit, Please change vehicle number plate'], 406);
                }
                $entryVehicle = new VehicleTransaction();                
                $entryVehicle->entry_interchange_id = $data->interchange->id;                
                $entryVehicle->number_plate = $data->number_plate;                
                $entryVehicle->entry_date_time = $data->date_time;                
                $entryVehicle->save();

                return response()->json(['message' => 'Vehicle entry added succuessfuly'], 200);
            }
            // vehicle exit
            if($request->type == 'exit'){
                // get entry information of the vehicle
                $vehicleInfo = VehicleTransaction::where('number_plate', $data->number_plate)
                    ->whereNull('exit_interchange_id')->first();
                // if no entry information found return error
                if(!$vehicleInfo){
                    return response()->json(['error' => 'This vehicle has no entry, Please enter valid vehicle number'], 404);
                }

                // get entry interchange information of the vehicle
                $vehicleEnteredInterchange = Interchange::select('id', 'name', 'km')->whereId($vehicleInfo->entry_interchange_id)->first();
                // if entry and exit interchagne are same then return error
                if($vehicleEnteredInterchange->id == $data->interchange->id){
                    return response()->json(['error' => 'Vehicle entry interchange and exit interchange must be different'], 406);
                }

                $totalKM = $data->interchange->km == 0 ? $vehicleEnteredInterchange->km : $data->interchange->km - $vehicleEnteredInterchange->km;
                $baseRete = 20;
                $distanRateKm = 0.2;
                $discountPercentage = 0;
                $dayOfWeek = Carbon::createFromDate($vehicleInfo->entry_date_time)->shortEnglishDayOfWeek;
                $isEvenNumber = (int)explode('-', $data->number_plate)[1] % 2 == 0 ? true : false;
                $isNationalHoliday = Carbon::createFromDate($vehicleInfo->entry_date_time)->format('m-d') == '03-23' 
                    || Carbon::createFromDate($vehicleInfo->entry_date_time)->format('m-d') == '08-14' 
                    || Carbon::createFromDate($vehicleInfo->entry_date_time)->format('m-d') == '12-25' ? true : false;

                // if weekend then rate per km will be 1.5x on rete per km determined by exit datetime
                if(Carbon::createFromDate($data->date_time)->isWeekend()){
                    $distanRateKm = $distanRateKm * 1.5;
                };

                // if day is Mon and Wed and vehicle number plate is even and not national holidy than discount 10% on the base of entry vehicle date time
                if(($dayOfWeek == 'Mon' || $dayOfWeek == 'Wed') && $isEvenNumber && !$isNationalHoliday)  $discountPercentage = 10;

                // if day is Tue and Thu and vehicle number plate is odd and not national holidy than discount 10% on the base of entry vehicle date time
                if(($dayOfWeek == 'Tue' || $dayOfWeek == 'Thu') && !$isEvenNumber && !$isNationalHoliday)  $discountPercentage = 10;

                // if national holiday then discount percentage will be 50
                if($isNationalHoliday) $discountPercentage = 50;

                $vehicleInfo->exit_interchange_id = $data->interchange->id;
                $vehicleInfo->exit_date_time = $data->date_time;
                $vehicleInfo->save();

                $distanceCost = $distanRateKm * $totalKM;
                $subTotal = $baseRete + $distanceCost;
                $discount = ($subTotal * ($discountPercentage / 100));
                $total = $subTotal - $discount;

                $obj = new stdClass();
                $obj->entryInterchange = $vehicleEnteredInterchange;
                $obj->exitInterchange = $data->interchange;
                $obj->totalKmTraveled = $totalKM;
                $obj->baseRate = $baseRete;
                $obj->distanRateKm = $distanRateKm;
                $obj->discountPercentage = $discountPercentage;
                $obj->distanceCost = $distanceCost;
                $obj->subTotal = $subTotal;
                $obj->discount = $discount;
                $obj->total = $total;

                return response()->json(['summary' => $obj, 'message' => 'Vehicle exit succuessfuly'], 200);

            }
        } catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // check number is enven or odd
    function checkNumberPlateIsEvenOrOdd($number)
    {
        return (int)explode('-', $number)[1] % 2 == 0 ? 'even' : 'odd';
    }

    // get day of week from date
    function getDayOfWeekByDate($date)
    {
        return Carbon::createFromDate($date)->shortEnglishDayOfWeek;
    }
}
