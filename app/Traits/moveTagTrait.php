<?php

namespace App\Traits;

use App\Models\Qs36f\{
    MaterialMoveTag,
    ForkScanLocation,
    AnoBars,
    AnoMoveTag
};
use Carbon\Carbon;

trait moveTagTrait
{

    /**
     * get move tag data
     *
     * @param   int     $tagNo  the move tag
     * @param   string  $dept   which dept the request is for
     *
     * @return  Collection move tag collection
     */
    public function initMoveTag(int $tagNo, string $dept)
    {
        try {
            $query = $this->getMoveTagData($tagNo);

            if (!$query) {
                throw new \Exception("Move Tag " . $tagNo . " not found");
            }

            if ((int)$query->mmt_qty === 0) {
                throw new \Exception("Tag Quantity is 0");
            }

            if ((string)$query->mmt_status === 'C') {
                throw new \Exception("Job is Closed");
            }

            $anoMoveTag = AnoMoveTag::where('tagno', $query->mmt_tagno)->first();

            if (!$anoMoveTag) {
                throw new \Exception("Tag is not loaded into this department", $query->mmt_tagno);
            }

            if ($anoMoveTag->deleted_on !== null) {
                throw new \Exception("This tag was deleted. Deleted On: " . Carbon::parse($anoMoveTag->deleted_on)->format('Y-m-d H:i:s'));
            }

            if ($query->mmt_todpt === $dept) {
                return $query;
            } else {
                throw new \Exception("Move tag not ready for anodize");
            }
        } catch (\Exception $e) {
            return [ 'code' => 422, 'message' => $e->getMessage() . '. TagNo: ' . $query->mmt_tagno ];
        }
    }

    /**
     * tmd description
     *
     * @param   int  $dpt        department
     * @param   int  $location   department location
     * @param   int  $plant      which plant
     *
     * @return  string
     */
    public function getTmdDescription(int $dpt, int $location, int $plant)
    {
        if($dpt == 'QC') {
            return 'QC';
        }

        $query = ForkScanLocation::select(
                'FSL_LOC',
                'FSL_DESC'
            )
            ->where('FSL_LOC', $location)
            ->where('FSL_DEPT', $dpt)
            ->where('FSL_PLT', $plant)
            ->first();

        if(!$query)
        {
            return 'Undecided '.$dpt;
        }

        return ucwords($query->fsl_desc);
    }

    /**
     * load tag into ano
     *
     * @param   int     $tagNo      the move tag
     * @param   bool    $undelete   should we restore this tag?
     *
     * @return \Illuminate\Http\Response
     */
    public function loadAnoTag(int $tagNo, bool $undelete)
    {
        try {
            $query = AnoMoveTag::where('tagno', $tagNo)->first();

            if ($query) {
                if ($undelete === true) {
                    $query->deleted_on = null;
                    $query->update();
                    return response()->json(['code' => 200, 'message' => 'Tag Number: ' . $tagNo . ' was restored']);
                } else if ($query->deleted_on !== null) {
                    throw new \Exception("This tag was deleted. Deleted On: " . Carbon::parse($query->deleted_on)->format('Y-m-d H:i:s'));
                } else {
                    throw new \Exception("Tag already scanned into anodize");
                }
            }

            $anoMoveTag = new AnoMoveTag();
            $anoMoveTag->tagno = $tagNo;
            $anoMoveTag->status = 'L';
            $anoMoveTag->save();

            if (!$anoMoveTag->exists) {
                throw new \Exception("Unable to scan tag. Please Contact IT.");
            }

            return response()->json(['code' => 200, 'message' => 'Tag Number: ' . $tagNo . ' was loaded into Anodize']);
        } catch (\Exception $e) {
            return [ 'code' => 422, 'message' => $e->getMessage() . '. TagNo: ' . $tagNo ];
        }
    }

    /**
     * load scanned bar code
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function loadScannedTag($request)
    {
         try {
            $query = $this->getAnoBar($request->tag, $request->bar);

            if (!$query) {
                throw new \Exception ('Bar not found');
            }

            if ($query->deleted_on !== null) {
                throw new \Exception ('Bar was deleted');
            }

            $moveTag = $this->getMoveTagData($request->tag);

            return response()->json(['code' => 200, 'message' => 'Loaded Step', 'bar' => $query, 'movetag' => $moveTag]);

        } catch (Exception $e) {
            return [ 'code' => 422, 'message' => $e->getMessage() ];
        }
    }

    /**
     * save tank data
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function saveTank($request)
    {
        try {
            $query = $this->getAnoBar($request->tag, $request->bar);

            if (!$query) {
                throw new \Exception('Could not find bar');
            }

            $query->tank_num = $request->tankNumber;
            $query->etch_time = $request->etchTime;
            $query->tank_time = $request->anoTime;
            $query->tank_temp = $request->anoTemp;

            if ($query->save()) {
                return response()->json(['code' => 200, 'message' => 'tank info saved']);
            }

        } catch (Exception $e) {
            return [ 'code' => 422, 'message' => $e->getMessage() ];
        }
    }

    /**
     * search for bar
     *
     * @param  integer  $tag
     * @param  integer  $bar
     *
     * @return AnoBars
     */
    private function getAnoBar(int $tag, int $bar): AnoBars
    {
        return AnoBars::where('tag', $tag)
                ->where('number', $bar)
                ->first();
    }

    /**
     * search for move tag
     *
     * @param  integer  $tag
     *
     * @return MaterialMoveTag
     */
    private function getMoveTagData($tag): MaterialMoveTag
    {
        return MaterialMoveTag::where('MMT_TAGNO', $tag)->first();
    }

    /**
     * get move tag id
     *
     * @param  integer  $tag
     *
     * @return int
     */
    private function getMoveTagID($tag)
    {
        $query = AnoMoveTag::where('tagno', $tag)->first();
        if ($query) {
            if ($query->deleted_on === null) {
                return $query->id;
            } else {
                return -1;
            }
        }

        return 0;
    }

    /**
     * get shift
     *
     * @return int
     */
    private function getShift()
    {
        $hour = Carbon::now()->setTimezone('America/Chicago')->format('G');
        switch ($hour) {
            case ( $hour >= 6 && $hour < 15 ):
                return 1;
            case ( $hour >= 15 && $hour < 23 ):
                return 2;
            default:
                return 3;
        }
    }
}
