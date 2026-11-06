<?php

namespace App\Enums;

/** Journey stages (brief §6). */
enum ProgramType: string
{
    use HasOptions;

    case DivisionalSummit = 'divisional-summit';
    case DistrictRoadshow = 'district-roadshow';
    case CampusProgram = 'campus-program';

    const LABELS = ['divisional-summit' => 'Divisional summit', 'district-roadshow' => 'District roadshow', 'campus-program' => 'Campus program'];

    /** The directory's URL segment: /divisional-summits, /district-roadshows, /campus-programs. */
    public function pathSegment(): string
    {
        return $this->value.'s';
    }
}
