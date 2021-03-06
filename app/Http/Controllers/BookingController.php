<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class BookingController extends Controller
{
    public function getAllBookings()
    {
        // $allBookings = DB::table('bookings')->get();
        // return $allBookings;
        $booking = DB::table('bookings as book')
            ->Join('users as au', 'book.driverAssignedId', '=', 'au.id')
            ->Join('users as cu', 'book.bookingUserId', '=', 'cu.id')
            ->select('book.*', 'au.userName as driver_userName', 'au.phone as driver_phone', 'au.vacancy as driver_vacancy', 'au.cabNumber as driver_cabNumber', 'au.email as driver_email', 'au.image as driver_image', 'cu.userName as user_userName', 'cu.phone as user_phone', 'cu.email as user_email', 'cu.image as user_image')
            ->get();
        return $booking;
    }

    public function addBooking(Request $request)
    {
        // return $request;
        $response = DB::table('users')->where('id', '=', $request->driverAssignId)->first();
        if ($response->isAvailable == "not-available") {
            return response()->json(
                [
                    "result" => "error",
                    "message" => "Driver not available!",
                    "title" => "Error",
                ], 403)->header('Content-Type', 'application/json');
        }
        $booking = DB::table('bookings')->insert(
            [
                'bookingUserId' => $request->bookingUserId,
                'driverAssignedId' => $request->driverAssignId,
                'bookingName' => $request->name,
                'bookingAddressFrom' => $request->origin,
                'bookingAddressTo' => $request->destination,
                'bookingLandmark' => $request->input('origin landMark'),
                'date' => $request->date,
                'time' => $request->time,
                'bookingPhone' => $request->contact,
                'bookingTripType' => $request->tripDetails,
                // 'bookingAmount' => $request->bookingAmount,
                // 'bookingCity'=>$request->bookingCity,
                // 'bookingState'=>$request->bookingState,
                // 'bookingZip'=>$request->bookingZip,
                // 'bookingCountry'=>$request->bookingCountry,
            ]
        );

        DB::table('users')->where('id', $request->driverAssignId)->update(['isAvailable' => 'not-available','updated_at' => DB::raw('CURRENT_TIMESTAMP')]);
        if ($booking) {
            $document = [
                "result" => "success",
                "message" => "Record saved successfully",
                "title" => "Success",
            ];
        } else {
            $document = [
                "result" => "error",
                "message" => "Record saving Failed!",
                "title" => "Error",
            ];
        }
        return response()->json($document, 200);
    }

    public function deleteBooking(Request $request, $id)
    {
        $row = DB::table('bookings')->where('id', '=', $id)->first();
        // return $row->driverAssignedId;
        $usrDel = DB::table('users')->where('id', $row->driverAssigedId)->update(['isAvailable' => 'available', 'updated_at'=>DB::raw('CURRENT_TIMESTAMP')]);
        $deleted = DB::table('bookings')->where('id', '=', $id)->delete();
        // return $deleted;
        if ($deleted) {
            $document = [
                "result" => "success",
                "message" => "Record deleted successfully",
                "title" => "Success",
            ];
        } else {
            $document = [
                "result" => "error",
                "message" => "Record deleting Failed!",
                "title" => "Error",
            ];
        }
        return response()->json($document, 200);
    }
    public function editBooking(Request $request){

    }

    public function getBooking($id)
    {
        $bookings = DB::table('bookings as book')
            ->Join('users as au', 'book.driverAssignedId', '=', 'au.id')
            ->Join('users as cu', 'book.bookingUserId', '=', 'cu.id')
            ->select('book.*', 'au.cabNumber as driver_cabNumber','au.userName as driver_name', 'cu.userName as user_userName')
            ->where('bookingUserId', '=', $id)
            ->orderBy('created_at', 'DESC')
            ->get();
        return $bookings;
    }

    public function getUserSingleBooking($id)
    {
        $booking = DB::table('bookings')->where('bookingUserId', '=', $id)->get();
        return $booking;
    }
    
    public function getDriverBookings($id)
    {
        $bookings = DB::table('bookings as book')
            ->Join('users as au', 'book.driverAssignedId', '=', 'au.id')
            ->Join('users as cu', 'book.bookingUserId', '=', 'cu.id')
            ->select('book.*', 'au.cabNumber as driver_cabNumber','au.userName as driver_name', 'cu.userName as user_userName')
            ->where('driverAssignedId', '=', $id)
            ->orderBy('created_at', 'DESC')
            ->get();
        return $bookings;
    }

    public function updateBooking(Request $request){
        $bookingUpdate = DB::table('bookings')->where('id', $request->bookingId)->update(['status' => 'complete','updated_at' => DB::raw('CURRENT_TIMESTAMP')]);
        if($bookingUpdate){
            $userUpdate = DB::table('users')->where('id', $request->driverId)->update(['isAvailable' => 'available','currentLocation' => $request->currentLocation,'updated_at' => DB::raw('CURRENT_TIMESTAMP')]);
        }
        if(isset($userUpdate)){
            return response()->json(
                [
                    "result" => "Success",
                    "message" => "Bookings updated Successfully!",
                    "title" => "Success",
                ], 200)->header('Content-Type', 'application/json');
        }else{
            return response()->json(
                [
                    "result" => "error",
                    "message" => "Booking update Failed!",
                    "title" => "Error",
                ], 403)->header('Content-Type', 'application/json');
        }
    }

    public function updateLocation(Request $request){
       
       
            $userUpdate = DB::table('users')->where('id', $request->driverId)->update(['currentLocation' => $request->currentLocation,'updated_at' => DB::raw('CURRENT_TIMESTAMP')]);
    
        if(isset($userUpdate)){
            return response()->json(
                [
                    "result" => "Success",
                    "message" => "Location updated Successfully!",
                    "title" => "Success",
                ], 200)->header('Content-Type', 'application/json');
        }else{
            return response()->json(
                [
                    "result" => "error",
                    "message" => "Location update Failed!",
                    "title" => "Error",
                ], 403)->header('Content-Type', 'application/json');
        }
    }
}
