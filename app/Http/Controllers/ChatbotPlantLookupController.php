<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PlantKnowledgeService;

class ChatbotPlantLookupController extends Controller
{
    public function lookup(Request $request, PlantKnowledgeService $service)
    {
        $q = $request->input('q') ?? $request->input('name');
        if (!$q) {
            return response()->json(['error' => 'Missing query param q or name'], 400);
        }

        $result = $service->findBestMatch($q);

        if (!$result) {
            return response()->json(['result' => null]);
        }

        return response()->json(['result' => $result]);
    }
}
