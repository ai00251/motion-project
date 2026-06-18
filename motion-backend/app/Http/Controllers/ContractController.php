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
    // pievieno lietotāju grupai un izveido jaunu līgumu
    public function joinGroup(Request $request, $groupId)
    {
        try {
            $user = Auth::user();
            $group = Group::findOrFail($groupId);

            // ja klienta ieraksta vēl nav, tas tiek izveidots automātiski
            $client = Client::firstOrCreate([
                "user_id" => $user->user_id,
            ]);

            // pārbauda, vai lietotājs jau nav šajā grupā
            $existingContract = Contract::where("client_id", $client->client_id)
                ->where("group_id", $groupId)
                ->first();

            if ($existingContract) {
                return response()->json(
                    [
                        "message" => "you are already enrolled in this group",
                        "success" => false,
                    ],
                    400
                );
            }

            // sagatavo līgumu ar reģistrācijas datumu, beigu datumu un mēneša maksu
            $contract = Contract::create([
                "client_id" => $client->client_id,
                "group_id" => $groupId,
                "registration_date" => Carbon::now(),
                "end_date" => Carbon::now()->addWeeks(8),
                "monthly_fee" => $this->calculateFee($group),
            ]);

            return response()->json(
                [
                    "message" => "successfully joined the group!",
                    "contract" => $contract,
                    "success" => true,
                ],
                201
            );
        } catch (\Exception $e) {
            // ja rodas kļūda, atgriež precīzu paziņojumu ar statusu 500
            return response()->json(
                [
                    "message" => "failed to join group: " . $e->getMessage(),
                    "success" => false,
                ],
                500
            );
        }
    }

    // aprēķina grupas maksu pēc tās līmeņa
    private function calculateFee($group)
    {
        $baseFees = [
            "beginner" => 120,
            "intermediate" => 150,
            "advanced" => 180,
        ];

        // ja līmenis nav definēts, izmanto noklusēto maksu
        return $baseFees[$group->level] ?? 140;
    }
}