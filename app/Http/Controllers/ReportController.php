<?php

namespace App\Http\Controllers;

use App\Http\Requests\Report\ReportStore;
use App\Http\Requests\Report\ReportUpdate;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    //
    public function index()
    {
        $reports = Report::with("author")->all();
        return response()->json($reports, 200);
    }
    public function show($id)
    {
        $report = Report::find($id)->with("author");
        return response()->json($report, 200);
    }
    public function store(ReportStore $request)
    {
        try {
            $input = $request->all();
            if ($report = Report::create($input)) {
                return response()->json($report, 200);
            }
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 404);
        }
    }
    public function update(ReportUpdate $request, $id)
    {
        try {
            $input = $request->all();
            $report = Report::find($id);
            if ($report->update($input)) {
                return response()->json($report, 200);
            }
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 404);
        }
    }
    public function delete($id)
    {
        try {
            $report = Report::find($id);
            if ($report->delete()) {
                return response()->json(["message" => "report successfully deleted"], 200);
            }
        } catch (\Throwable $th) {
            return response()->json(["message" => $th->getMessage()], 404);
        }
    }
}
