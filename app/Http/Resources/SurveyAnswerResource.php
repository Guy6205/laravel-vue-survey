<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use DateTime;

class SurveyAnswerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'survey' => new SurveyResource($this->survey),
            'end_date' => (new DateTime($this->end_date))->format('Y-m-d H:i:s'),
        ];
    }
}
