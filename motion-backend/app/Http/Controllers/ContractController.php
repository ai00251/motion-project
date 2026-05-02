<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Group;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ContractController extends Controller
{
    public function joinGroup(Request $request, $groupId)
    {
        try {
            $user = Auth::user();
            $group = Group::findOrFail($groupId);

            $client = Client::firstOrCreate([
                "user_id" => $user->user_id,
            ]);

            $existingContract = Contract::where("client_id", $client->client_id)
                ->where("group_id", $groupId)
                ->first();

            if ($existingContract) {
                return response()->json(
                    [
                        "message" => "You are already enrolled in this group",
                        "success" => false,
                    ],
                    400
                );
            }

            $contract = Contract::create([
                "client_id" => $client->client_id,
                "group_id" => $groupId,
                "registration_date" => Carbon::now(),
                "end_date" => Carbon::now()->addWeeks(8),
                "monthly_fee" => $this->calculateFee($group),
            ]);

            return response()->json(
                [
                    "message" => "Successfully joined the group!",
                    "contract" => $contract,
                    "success" => true,
                ],
                201
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Failed to join group: " . $e->getMessage(),
                    "success" => false,
                ],
                500
            );
        }
    }

    private function calculateFee($group)
    {
        $baseFees = [
            "beginner" => 120,
            "intermediate" => 150,
            "advanced" => 180,
        ];

        return $baseFees[$group->level] ?? 140;
    }
}
