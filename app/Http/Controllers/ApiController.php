<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\Summary;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ApiController extends Controller
{
    function getUserIdByAuthorizationCode($code):int|bool{
        $user = User::where('authorization_code', $code)->first();
        if (empty($user))
            return false;
        else
            return $user->id;
    }

    function getTitle($date){
        $currentDate = Carbon::createFromFormat('d/m/Y',  date('d/m/Y')); // Replace '31/07/2023' with your current date
        $otherDate = Carbon::createFromFormat('d/m/Y', $date); // Replace '27/07/2023' with the other date you want to compare

        $daysDifference = $currentDate->diffInDays($otherDate);
        $monthInLetters = $otherDate->format('F');
        $monthsDifference = $currentDate->diffInMonths($otherDate);
        $dayInLetters = $otherDate->format('l');
        $yearString = $otherDate->format('Y');

        if ($daysDifference === 0 )
            return "Today";
        if ($daysDifference === 1)
            return "Yesterday";
        if ($daysDifference > 1 && $daysDifference <= 7)
            return  "Last ".$dayInLetters;
        if ($daysDifference > 7 && $monthsDifference < 2 )
            return  "Last month";
        if ($daysDifference > 7 && $monthsDifference < 11)
            return "Last ".$monthInLetters;
        if ($daysDifference > 7 && $monthInLetters > 11)
            return $monthInLetters." ".$yearString;
        return "Tomorrow?";
    }

    public function getSummaries(Request $request){
        $userId = $this->getUserIdByAuthorizationCode($request->AuthorizationKey);
        $summaries = Summary::where('userId', $userId)->get();

        $summariesArray = $summaries->toArray();

        usort($summariesArray, function ($a, $b) {
            $dateA = DateTime::createFromFormat('d/m/Y', $a['date']);
            $dateB = DateTime::createFromFormat('d/m/Y', $b['date']);

            return $dateA <=> $dateB;
        });
        $sortedSummaries = new Collection($summariesArray);
        $returnSummaries = [];
        foreach ($sortedSummaries as $sortedSummary){

            $entries = Entry::where('summary_id', $sortedSummary["id"])->get();
            $returnSummaries[] = [
                "title" => $this->getTitle($sortedSummary["date"]),
                "date" => $sortedSummary["date"],
                "entries" => $entries
            ];
        }
        return json_encode($returnSummaries);
    }

    public function postSummary(Request $request){
        $userId = $this->getUserIdByAuthorizationCode($request->AuthorizationKey);

        $validator = Validator::make($request->all(), [
            'date' => ['required', 'string'],
            'name' => ['required', 'string'],
            'calories' => ['integer', 'required'],
            'time' => ['required', 'string'],
            'price' => ['required', 'integer']
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $summary = Summary::where('date', $request->date)->first();
        if (empty($summary)){
            $summary = new Summary();
            $summary->date = $request->date;
            $summary->userId = $userId;
            $summary->save();
        }

        $entry = new Entry();
        $entry->summary_id = $summary->id;
        $entry->name = $request->name;
        $entry->calories = $request->calories;
        $entry->price = $request->price;
        $entry->time = $request->time;
        $entry->save();

        return json_encode([
            "message" => "created succesfully"
        ]);

    }



    public function testAuthentication(Request $request){
        $userId = $this->getUserIdByAuthorizationCode($request->AuthorizationKey);
        $message = "";
        if ($userId === false)
            $message = "Couldn't connect to user";
        else
            $message = "Succelfully connected";
        return response()->json([
            'message' => $message
        ]);
    }
}
