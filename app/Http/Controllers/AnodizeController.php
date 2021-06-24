<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\moveTagTrait;
use App\Models\Qs36f\{
    MaterialMoveTag,
    ForkScanLocation,
    AnoMoveTag,
    AnoBars
};
use Carbon\Carbon;

/**
 * controller for anodize api calls
 */
class AnodizeController extends Controller
{
    use moveTagTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $menu = [
            [
                'name' => 'movetag',
                'text' => 'Move Tag'
            ]
        ];
        return response()->json(
            [
                'menu' => $menu
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        \App::Abort(422, 'Unknown Path');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request)
    {
        \App::Abort(422, 'Unknown Path');
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $tagNo = null;
        $step = null;
        $bar = null;

        preg_match_all('/\d+/', $request->movetag, $matches);
        $tag = $matches[0];

        $tagNo = $tag[0];

        $moveTag = $this->initMoveTag($tagNo, 'ANO');

        if ($moveTag instanceof MaterialMoveTag) {
            return response()->json(['code' => 200, 'data' => $moveTag]);
        } else {
            return response()->json($moveTag);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function edit(int $id)
    {
        \App::Abort(422, 'Unknown Path');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(Request $request, int $id)
    {
        \App::Abort(422, 'Unknown Path');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $id)
    {
        $tag = AnoMoveTag::where('tagno', $id)->first();
        $tag->deleted_on = Carbon::now()->setTimezone('America/Chicago')->format('Y-m-d H:i:s');

        if ($tag->update()) {
            return response()->json(['code' => 200, 'message' => 'deleted ano tag ' . $id]);
        }
    }

    /**
     * Print barcode
     *
     * @param  string  $code
     * @return \Illuminate\Http\Response
     */
    public function printLabel(string $code)
    {
        $zpl = "
            ^XA
            ^FX section with bar code.
            ^BY2,3,75
            ^FO45,0^BC^FD". $code ."^FS
            ^XZ
        ";

        try {
            $fp=pfsockopen("10.0.0.179",9100);
            $response = fputs($fp,$zpl);
            fclose($fp);

            return response()->json(['code' => 200, 'message' => 'printed barcode ' . $code]);
        }
        catch (Exception $e)
        {
            return [ 'code' => 422, 'message' => $e->getMessage() ];
        }
    }

    /**
     * load tag into ano department table
     *
     * @param   int    $tagNo     move tag
     * @param   bool   $undelete  should we undelete this tag?
     *
     * @return \Illuminate\Http\Response
     */
    public function loadTag(int $tagNo, bool $undelete = false)
    {
        return $this->loadAnoTag($tagNo, $undelete);
    }

    /**
     * load step based on passed in info
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function loadStep(Request $request)
    {
       return $this->loadScannedTag($request);
    }

    /**
     * create bar in database table
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function createBars(Request $request)
    {
        try {
            $query = AnoBars::where('tag', $request->tag)
                ->where('number', $request->number)
                ->first();
            $moveTagID = $this->getMoveTagID($request->tag);

            if ($moveTagID === 0) {
                throw new \Exception('Move tag not loaded into anodize');
            } else if ($moveTagID === -1) {
                throw new \Exception('Move tag was loaded but deleted in anodize');
            }

            if (!$query) {
                $anobar = new AnoBars();
                $anobar->shift = $this->getShift();
                $anobar->status = 'L';
                $anobar->step = $request->step;
                $anobar->number = $request->number;
                $anobar->tagID = $moveTagID;
                $anobar->tag = $request->tag;

                if ($anobar->save()) {
                    $barcode = $request->tag . '-' . $request->step . '-' . $request->number;

                    return response()->json(['code' => 200, 'message' => 'Bar created', 'barcode' => $barcode]);
                }
            } else {
                if ($query->deleted_on !== null) {
                    return response()->json(['code' => 422, 'message' => "Bar was deleted on: " . $query->deleted_on]);
                } else {
                    $barcode = $request->tag . '-' . $request->step . '-' . $request->number;
                    return response()->json(['code' => 200, 'message' => "Bar exists", 'barcode' => $barcode]);
                }
            }


        } catch (Exception $e) {
            return [ 'code' => 422, 'message' => $e->getMessage() ];
        }
    }

    /**
     * save tank info into database
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function saveTankInfo(Request $request)
    {
        return $this->saveTank($request);
    }
}
